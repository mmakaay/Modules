<?php

if(!defined("PHORUM")) return;

// Register the additional CSS code for this module.
function phorum_mod_bbcode_spoiler_css_register($data)
{
    // So we can use {URL->HTTP_PATH} in the templates.
    // This is a work around for Phorum 5.2.2-beta, which
    // missed the call to the URL building function.
    if (!isset($GLOBALS['PHORUM']['DATA']['URL']['HTTP_PATH'])) {
        phorum_build_common_urls();
    }

    $data['register'][] = array(
        "module" => "bbcode_spoiler",
        "where"  => "after",
        "source" => "template(bbcode_spoiler::css)"
    );
    return $data;
}

// Register the additional JavaScript code for this module.
function phorum_mod_bbcode_spoiler_javascript_register($data)
{
    $data[] = array(
        "module" => "bbcode_spoiler",
        "source" => "file(mods/bbcode_spoiler/bbcode_spoiler.js)"
    );
    return $data;
}

function phorum_mod_bbcode_spoiler_common()
{
    global $PHORUM;
    $lang = $PHORUM['DATA']['LANG']['mod_bbcode_spoiler'];

    // Setup the URL for this module's Ajax handler.
    $ajaxurl = phorum_get_url(PHORUM_ADDON_URL, "module=bbcode_spoiler");

    // Add required Javascript to the <head> section of the page.
    // We do not use javascript_register for these, because forums
    // that use a lot of languages might generate a lot of cache files.
    // For those it might be best just to simply put this in the <head>
    // section of the pages.
    $PHORUM["DATA"]["HEAD_TAGS"] .=
        '<script type="text/javascript">' .
        "var bbcode_spoiler_ajax_url = '$ajaxurl';\n" .
        "editor_tools_lang['enter spoiler description'] = " .
        "'" . $lang['enter spoiler description'] . "';\n" .
        "editor_tools_lang['enter spoiler content'] = " .
        "'" . $lang['enter spoiler content'] . "';\n" .
        '</script>';
}

/**
 * Parse the spoiler tree.
 *
 * This function is used to parse the message body into separate spoiler
 * blocks, which are possibly organized in a tree structure, because of
 * tag nesting.
 *
 * @param array $message
 *     A Phorum message array.
 *
 * @return mixed
 *     NULL if there aren't any spoilers available in the message.
 *     Array, containing a hierarchical representation of the available
 *     spoilder nodes.
 */
function bbcode_spoiler_parse_tree($message)
{
    static $start  = '\[spo(?:iler)?(?:[\s=][^\]\[]+)?\]';
    static $end    = '\[\/spo(?:iler)?\]';
    static $pbr    = '<phorum break>';

    $body = $message;
    if (is_array($message)) {
        if (!isset($message["body"])) return NULL;
        $body = $message['body'];
    }
    if (!strstr($body, '[spo')) return NULL;

    $PHORUM        = $GLOBALS['PHORUM'];
    $lang          = $PHORUM['DATA']['LANG']['mod_bbcode_spoiler'];
    $spoiler_nr    = 0;
    $active_nr     = 0;
    $stack         = array();

    // Fiddle with white space around spoiler tags.
    $body = preg_replace("!\s*(?:$pbr)?($start|$end)\s*(?:$pbr)?!", "$1", $body);

    // Initialize tree root.
    $tree = array(0 => array(
        'parent'  => NULL,
        'content' => array()
    ));

    // Split into tokens.
    $m = preg_split("/($start|$end)/", $body, -1, PREG_SPLIT_DELIM_CAPTURE);

    foreach ($m as $token)
    {
        // Match [spoiler] start tag.
        if (preg_match("/^$start$/", $token, $mt))
        {
            array_push($stack, $active_nr);

            $spoiler_nr ++;

            // Determine the title to use for this spoiler.
            $title = preg_replace('/^\[spo(?:iler)?[\s=]?|\]$/', '', $token);
            $title = trim($title);
            if ($title == '') $title = $lang['ShowSpoiler'];

            $tree[$spoiler_nr] = array(
                'nr'      => $spoiler_nr,
                'parent'  => $active_nr,
                'title'   => $title,
                'content' => array(),
                'visible' => 0
            );

            $tree[$active_nr]['content'][] =& $tree[$spoiler_nr];

            $active_nr = $spoiler_nr;
        }
        // Match [/spoiler] closing tag.
        elseif (preg_match("/^$end$/", $token))
        {
            $active_nr = empty($stack) ? 0 : array_pop($stack);
        }
        // Match content.
        else
        {
            $tree[$active_nr]['content'][] = $token;
        }
    }

    // We might have this in case a spoiler tag was opened, without closing it.
    if (empty($tree[1])) return NULL;

    return $tree;
}

/**
 * This function will build a formatted message body, based on output
 * from the {@link bbcode_spoiler_parse_tree()} function.
 *
 * @param array $tree
 *     A spoiler tree as created by {@link bbcode_spoiler_parse_tree()}.
 *
 * @param array $message
 *     The message data array for the message that is being formatted.
 *
 * @return string $body
 *     The formatted message body.
 */
function bbcode_spoiler_format_tree($tree, $message)
{
    static $tpl = NULL;

    // Work around for sloppy formatting calls, where the caller only
    // sends the message body (e.g. the meta descriptions module, which
    // only formats to get rid of the HTML code afterwards). This will
    // producing non working code. If another module suffers from that,
    // the other module should fix the format message data.
    if (!isset($message['message_id'])) { $message['message_id'] = 1; }
    if (!isset($message['forum_id']))   { $message['forum_id']   = 1; }
    if (!isset($message['thread']))     { $message['thread']     = 1; }

    $PHORUM = $GLOBALS['PHORUM'];
    $lang   = $PHORUM['DATA']['LANG']['mod_bbcode_spoiler'];

    if ($tpl === NULL) {
        ob_start();
        include(phorum_get_template('bbcode_spoiler::spoiler'));
        $tpl = ob_get_contents();
        ob_end_clean();
    }

    $body = '';

    foreach ($tree['content'] as $part)
    {
        // Handle spoiler.
        if (is_array($part))
        {
            // The id that we use for referencing this spoiler in the pages.
            $spoiler_id = $message['message_id'] . '_' . $part['nr'];

            // Setup info for spoilers in the message preview.
            if (empty($message['message_id']) || isset($_POST['preview']))
            {
                $view_url = "#";
                $content = bbcode_spoiler_format_tree($part, $message);
                $display = 'block';
                $link_class = 'bbcode_spoiler_link_view';
            }
            // Setup info for invisible spoilers.
            elseif (empty($part['visible']))
            {
                // The URL that can be used by non-javascript enabled browsers
                // to view the spoiler content.
                $view_url = phorum_get_url(
                    PHORUM_FOREIGN_READ_URL,
                    $message['forum_id'],
                    $message['thread'],
                    $message['message_id'],
                    "view_spoiler=".$message['message_id'].":".$part['nr']
                );

                $view_url = preg_replace(
                    '/#msg-\d+$/',
                    '#bbcode_spoiler_anchor_'.$spoiler_id,
                    $view_url
                );

                $content = '';
                $display = 'none';
                $link_class = 'bbcode_spoiler_link';
            }
            // Setup info for visible spoilers.
            else
            {
                // A standard read URL to hide the visible spoiler(s).
                $view_url = phorum_get_url(
                    PHORUM_FOREIGN_READ_URL,
                    $message['forum_id'],
                    $message['thread'],
                    $message['message_id']
                );

                $content = bbcode_spoiler_format_tree($part, $message);
                $display = 'block';
                $link_class = 'bbcode_spoiler_link_view';
            }

            // Determine the title to use for this spoiler.
            $title = $part['title'];
            if ($title == '') $title = $lang['ShowSpoiler'];

            // Add the formatted spoiler to the body.
            $body .= str_replace(
                array(
                    '%spoiler_id%',
                    '%spoiler_title%',
                    '%spoiler_view_url%',
                    '%spoiler_content%',
                    '%spoiler_display%',
                    '%spoiler_link_class%'

                ),
                array(
                    $spoiler_id,
                    $title,
                    $view_url,
                    $content,
                    $display,
                    $link_class
                ),
                $tpl
            );
        }
        // Handle standard body content.
        else {
            $body .= $part;
        }

    }

    $body = preg_replace('/^(?:\s|<br\s?\/>)+/', '', $body);
    $body = preg_replace('/(?:\s|<br\s?\/>)+$/', '', $body);

    return $body;
}


function phorum_mod_bbcode_spoiler_format($data)
{
    $PHORUM = $GLOBALS["PHORUM"];

    // If we are in the control center, then we are probably watching a
    // signature spoiler. If signature spoilers are not allowed, then
    // we are done.
    if (phorum_page == 'control' &&
        empty($PHORUM["mod_bbcode_spoiler"]["allow_in_signature"])) {
        return $data;
    }

    // Can be set by the addon handler to suspend spoiler formatting.
    if (!empty($GLOBALS["PHORUM"]["mod_bbcode_spoiler"]["skip_format"])) {
        return $data;
    }

    // For supporting non-javascript browsers, we need to honor
    // spoilers in the URL that need to be made visible. The format
    // for the URL parameter is:
    // view_spoiler=<message_id>:<spoiler 1>:<spoiler 2>:..:<spoiler n>
    $visible_spoilers = NULL;
    $visible_message_id = 0;
    if (!empty($PHORUM['args']['view_spoiler'])) {
        $visible = explode(":", $PHORUM['args']['view_spoiler']);
        $visible_message_id = array_shift($visible);
        foreach ($visible as $nr) {
            if (!empty($nr)) $visible_spoilers[] = (int) $nr;
        }
    }

    // Apply the spoiler formatting to the message bodies.
    foreach($data as $id => $message)
    {
        // Skip formatting if bbcode formatting was disabled for the post
        // (this is a feature of the BBcode module that we should honor).
        if (!empty($PHORUM["mod_bbcode"]["allow_disable_per_post"]) &&
            !empty($message['meta']['disable_bbcode'])) {
            continue;
        }

        // This check is done in bbcode_spoiler_parse_tree() too, but for
        // skipping an additional function call, we check it here as well.
        if (!isset($message["body"]) ||
            !strstr($message['body'], '[spo')) continue;

        // Work around for (sloppy) formatting calls, where the caller only
        // sends the message body (e.g. the meta descriptions module, which
        // only formats to get rid of the HTML code afterwards). This will
        // producing non working spoiler code. If another module suffers
        // from that, the calling module should fix the format message data.
        // This will also take care of formatting calls that are not
        // forum message based (e.g. spoilers in PM or previewing a
        // signature containing a spoiler in the control center).
        if (!isset($message['message_id'])) { $message['message_id'] = 0; }
        if (!isset($message['forum_id']))   { $message['forum_id']   = 0; }
        if (!isset($message['thread']))     { $message['thread']     = 0; }

        // Parse the spoiler tree structure. If no tree structure was
        // found, we can continue to the next message.
        $tree = bbcode_spoiler_parse_tree($message);
        if ($tree === NULL) continue;

        // If we are handling the visibility of spoilers through standard
        // URL parameters, then fix the visibility in the tree. If a nested
        // spoiler is requested, then its parents are set to visible as well.
        if (!empty($visible_message_id) &&
            $visible_message_id == $message['message_id'] &&
            !empty($visible_spoilers)) {

            foreach ($visible_spoilers as $visible) {
                if (empty($tree[$visible])) continue;
                for(;;) {
                    $tree[$visible]['visible'] = 1;
                    if (empty($tree[$visible]['parent'])) break;
                    $visible = $tree[$visible]['parent'];
                }
            }
        }

        // If we are in the user control center, then we are probably viewing
        // a stand-alone signature spoiler. In that case, we have no message
        // to work with in the Ajax request handling code and we have to make
        // spoilers visible at load time.
        /*
        TODO CHECK
        if (phorum_page == 'control') {
            foreach ($tree as $tid => $tdata) {
                if (isset($tdata['visible'])) {
                    $tree[$tid]['visible'] = 1;
                }
            }
        }
        */

        // Format the message body.
        $body = bbcode_spoiler_format_tree($tree[0], $message);
        $data[$id]['body'] = $body;

        continue;
    }

    return $data;
}

function phorum_mod_bbcode_spoiler_cc_save_user($user)
{
    global $PHORUM;

    // If another module returned an error already, then we won't run
    // our checks right now.
    if (isset($user["error"])) return $user;

    // If spoilers in signatures are allowed, then we're done.
    if (!empty($PHORUM["mod_bbcode_spoiler"]["allow_in_signature"])) {
        return $user;
    }

    // We only need to handle checks if the signature is being saved.
    if (!isset($user['signature'])) return $user;

    // Parse the spoiler tree structure for the signature.
    $tree = bbcode_spoiler_parse_tree($user['signature']);

    if (count($tree) > 1) {
        $lang = $PHORUM['DATA']['LANG']['mod_bbcode_spoiler'];
        $user['error'] = $lang['DenySignatureSpoiler'];
    }

    return $user;
}

function phorum_mod_bbcode_spoiler_addon()
{
    global $PHORUM;

    if (($post_data = file_get_contents('php://input')) === false |
        empty($post_data)) {
        print "<h1>BBcode spoiler error</h1>" .
              "Missing bbcode spoiler addon request in POST data.";
        exit;
    }

    if (preg_match('/retrieve (\d+)_(\d+)/', $post_data, $m))
    {
        $message_id = (int) $m[1];
        $spoiler    = (int) $m[2];

        // Retrieve the message from the database.
        $message = phorum_db_get_message($message_id);
        if (empty($message)) {
            print "<h1>BBcode spoiler error</h1>" .
                  "Message id $message_id not found.";
            exit;
        }

        // Add the signature to the message body, if users are allowed to
        // use spoilers in the signature.
        if (!empty($PHORUM["mod_bbcode_spoiler"]["allow_in_signature"]) &&
            !empty($message['user_id']) &&
            !empty($message['meta']['show_signature'])) {

            $user = phorum_api_user_get($message['user_id']);
            if (isset($user['signature'])) {
                $sig = trim($user['signature']);
                if ($sig != '') {
                    $message['body'] .= "\n\n$sig";
                }
            }
        }

        // Format the message. Set a variable to flag this module's format
        // hook to suspend the spoiler formatting at this point.
        require_once('./include/format_functions.php');
        $PHORUM["mod_bbcode_spoiler"]["skip_format"] = 1;
        $message['attachments'] = empty($message['meta']['attachments'])
                                ? array()
                                : $message['meta']['attachments'];
        $messages = array($message['message_id'] => $message);
        $messages = phorum_format_messages($messages);
        $message = $messages[$message['message_id']];
        $PHORUM["mod_bbcode_spoiler"]["skip_format"] = 0;

        // Parse the spoiler tree structure.
        $tree = bbcode_spoiler_parse_tree($message);

        // Check if the requested spoiler part is available.
        if (empty($tree) || empty($tree[$spoiler])) {
            print "<h1>BBcode spoiler error</h1>" .
                  "Spoiler nr $spoiler not found in message id $message_id.";
            exit;
        }

        $part = bbcode_spoiler_format_tree($tree[$spoiler], $message);

        // Interoperate with the in body attachment module: add code
        // for reinitializing the in body attachment viewer for possibly
        // added images.
        if (function_exists("in_body_attachment_viewer_reinit")) {
            $part .= in_body_attachment_viewer_reinit();
        }

        header("Content-Type: text/plain; " .
               "charset=".htmlspecialchars($PHORUM['DATA']['CHARSET']));
        print $part;
        exit;
    }
    else {
        print "<h1>BBcode spoiler error</h1>" .
              "Unrecognized addon request.";
        exit;
    }
}

function phorum_mod_bbcode_spoiler_editor_tool_plugin()
{
    $PHORUM = $GLOBALS['PHORUM'];
    $lang   = $PHORUM['DATA']['LANG']['mod_bbcode_spoiler'];

    if (empty($PHORUM["mod_bbcode_spoiler"]["enable_editor_tool"])) return;

    editor_tools_register_tool(
        'bbcode_spoiler',               // Tool id
        $lang['ToolButton'],            // Tool description
        './mods/bbcode_spoiler/templates/spoiler.gif', // Tool button icon
        'bbcode_spoiler_editor_tool()'  // Javascript action on button click
    );
}

?>
