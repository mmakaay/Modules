<?php

if(!defined("PHORUM")) return;

// Apply default setting.
if (!isset($PHORUM["mod_embed_mp3"]["enable_editor_tool"])) {
    $PHORUM["mod_embed_mp3"]["enable_editor_tool"] = 0;
}
if (!isset($PHORUM["mod_embed_mp3"]["embed_all_attachments"])) {
    $PHORUM["mod_embed_mp3"]["embed_all_attachments"] = 0;
}

// Because we had troubles with getting the default file download URL
// to work for the player (it was cut at the first comma), we had to
// put this trick in place to have a more safe URL for the MP3 file.
function phorum_mod_embed_mp3_parse_request()
{
    if (phorum_page == 'file' && isset($_GET['embed_mp3_query'])) {
        global $PHORUM_CUSTOM_QUERY_STRING;
        $PHORUM_CUSTOM_QUERY_STRING = base64_decode($_GET['embed_mp3_query']);
    }
}

// Each player on a page should have its own unique id.
// This is the counter that we use for this.
$GLOBALS['PHORUM']['mod_embed_mp3']['player_id'] = 0;

function phorum_mod_embed_mp3_javascript_register($data)
{
    $data[] = array(
        'module' => 'embed_mp3',
        'source' => 'file(mods/embed_mp3/audio-player/audio-player.js)'
    );

    $data[] = array(
        "module"    => "embed_mp3",
        "source"    => "file(mods/embed_mp3/embed_mp3.js)"
    );

    return $data;
}

function phorum_mod_embed_mp3_common()
{
    global $PHORUM;
    $lang = $PHORUM['DATA']['LANG']['embed_mp3'];

    if (empty($PHORUM["mod_embed_mp3"]["enable_editor_tool"])) return;

    // Add required Javascript to the <head> section of the page.
    // We do not use javascript_register for these, because forums
    // that use a lot of languages might generate a lot of cache files.
    // For those it might be best just to simply put this in the <head>
    // section of the pages.
    $PHORUM["DATA"]["HEAD_TAGS"] .=
        '<script type="text/javascript">' .
        "editor_tools_lang['enter mp3 url'] = " .
        "'" . $lang['enter mp3 url'] . "';\n" .
        "editor_tools_lang['invalid mp3 url'] = " .
        "'" . $lang['invalid mp3 url'] . "';\n" .
        '</script>';
}

function phorum_mod_embed_mp3_css_register($data)
{
    global $PHORUM;

    // So we can use {URL->HTTP_PATH} in the templates.
    // This is a work around for Phorum 5.2.2-beta, which
    // missed the call to the URL building function.
    if (!isset($PHORUM['DATA']['URL']['HTTP_PATH'])) {
        phorum_build_common_urls();
    }

    $data['register'][] = array(
        'module' => 'embed_mp3',
        'where'  => 'after',
        'source' => 'template(embed_mp3::css)'
    );
    return $data;
}

// Editor tool implementation. The editor_tools module needs to
// be enabled for this to work.
function phorum_mod_embed_mp3_editor_tool_plugin()
{
    $PHORUM = $GLOBALS['PHORUM'];
    if (empty($PHORUM["mod_embed_mp3"]["enable_editor_tool"])) return;
    $lang = $PHORUM['DATA']['LANG']['embed_mp3'];
    $base = $PHORUM['http_path'];

    editor_tools_register_tool(
        'embed_mp3',                      // Tool id
        $lang['ToolButton'],              // Tool description
        $base.'/mods/embed_mp3/icon.gif', // Tool button icon
        'embed_mp3_editor_tool()'         // Javascript action on button click
    );
}

// Format [mp3]...[/mp3] links in the message.
function phorum_mod_embed_mp3_format($data)
{
    // So we can use {URL->HTTP_PATH} in the templates.
    // This is a work around for Phorum 5.2.2-beta, which
    // missed the call to the URL building function.
    if (!isset($PHORUM['DATA']['URL']['HTTP_PATH'])) {
        phorum_build_common_urls();
    }

    $PHORUM = $GLOBALS["PHORUM"];

    // Run the replacements on the message bodies.
    foreach($data as $id => $message)
    {
        // Skip formatting if bbcode formatting was disabled for the post
        // (this is a feature of the BBcode module that we should honor).
        if (!empty($PHORUM["mod_bbcode"]["allow_disable_per_post"]) &&
          !empty($message['meta']['disable_bbcode'])) {
          continue;
        }

        // Skip formatting if there is no body to format.
        if (!isset($message["body"]) ||
            !strstr($message['body'], "[mp3]")) continue;

        // Find all [mp3]....[/mp3] BBcode tags.
        if (preg_match_all("/\[mp3\]((?:http|https|ftp):\/\/[a-z0-9;\/\?:@=\&\$\-_\.\+!*'\(\),~%# ]+?)\[\/mp3\]/is", $message['body'], $m))
        {
            foreach ($m[0] as $mid => $full)
            {
                // Try to determine the MP3 filename. If we cannot find
                // a reliable name (ending in .mp3), we fallback to a
                // safe option.
                $name = htmlspecialchars(urldecode(basename($m[1][$mid])));
                $name = preg_replace('/\?.*$/', '', $name);
                $ext = strtolower(preg_replace('/^.*\./', '', $name));
                if ($ext != 'mp3') {
                    $name = $PHORUM['DATA']['LANG']['AttachOpen'] . ' MP3';
                }

                // Setup the template data.
                $pid = $GLOBALS['PHORUM']['mod_embed_mp3']['player_id']++;
                $PHORUM['DATA']['PLAYER'] = array(
                    'ID'        => $pid,
                    'URL'       => htmlspecialchars($m[1][$mid]),
                    'FLASHURL'  => rawurlencode($m[1][$mid]),
                    'NAME'      => $name
                );

                // Load the template and catch the output.
                ob_start();
                include(phorum_get_template('embed_mp3::player'));
                $replace = ob_get_contents();
                ob_end_clean();

                // Replace the [mp3] tag in the body. The final replacement
                // will be done in the format_fixup hook.
                $tag = '[embed_mp3 '.md5($replace).']';
                $GLOBALS['PHORUM']['mod_embed_mp3']['format_fixup'][$id][$tag]
                    = $replace;
                $data[$id]['body'] = str_replace($full, $tag, $data[$id]['body']);
            }
        }
    }

    return $data;
}

function phorum_mod_embed_mp3_render_embedded_attachment($attachment, $message)
{
    $PHORUM = $GLOBALS['PHORUM'];

    // So we can use {URL->HTTP_PATH} in the templates.
    // This is a work around for Phorum 5.2.2-beta, which
    // missed the call to the URL building function.
    if (!isset($PHORUM['DATA']['URL']['HTTP_PATH'])) {
        phorum_build_common_urls();
    }

    // Check if the attachment is an MP3 file.
    if (!preg_match('/\.mp3$/i', $attachment['name'])) return $attachment;

    // Base64 trick to work around a problem with the player, which would
    // cut the URL at the comma (maybe for implementing playlists or so,
    // didn't really care to look it up).
    $flashurl = phorum_get_url(
        PHORUM_CUSTOM_URL,
        "file", FALSE, "embed_mp3_query=" .
        base64_encode($message['forum_id'].",file=".$attachment['file_id'])
    );

    // Setup the template data.
    $pid = $GLOBALS['PHORUM']['mod_embed_mp3']['player_id']++;
    $PHORUM['DATA']['PLAYER'] = array(
        'ID'        => $pid,
        'URL'       => htmlspecialchars($attachment['url']),
        'FLASHURL'  => rawurlencode($flashurl),
        'DOWNLOAD'  => htmlspecialchars($attachment['download_url']),
        'NAME'      => htmlspecialchars($attachment['description'])
    );

    // Load the template to render the MP3 player.
    ob_start();
    include(phorum_get_template('embed_mp3::player'));
    $attachment['rendered'] = ob_get_contents();
    ob_end_clean();

    return $attachment;
}

function phorum_mod_embed_mp3_format_fixup($messages)
{
    $PHORUM = $GLOBALS['PHORUM'];

    // If the admin configured the Embed MP3 module to display all
    // MP3 attachments as an embedded player, then we add them to the
    // body here.
    if (!empty($PHORUM['mod_embed_mp3']['embed_all_attachments']))
    {
        foreach ($messages as $id => $message)
        {
            // Some quick skip options.
            if (!isset($message['body']) ||
                empty($message['attachments'])) continue;

            $first = TRUE;
            foreach ($message['attachments'] as $aid => $attachment) {
                if (preg_match('/\.mp3$/', $attachment['name'])) {
                    $att = phorum_mod_embed_mp3_render_embedded_attachment(
                        $attachment, $message
                    );
                    if ($first) {
                        $messages[$id]['body'] .= '<br/>';
                        $first = FALSE;
                    }
                    $messages[$id]['body'] .= $att['rendered'];
                    unset($messages[$id]['attachments'][$aid]);
                }
            }
        }
    }

    if (!empty($PHORUM['mod_embed_mp3']['format_fixup'])) {
        $format_fixup = $PHORUM['mod_embed_mp3']['format_fixup'];
        foreach ($format_fixup as $id => $fixes) {
            if (!isset($messages[$id])) continue;
            foreach ($fixes as $tag => $content) {
                $messages[$id]['body']
                    = str_replace($tag, $content, $messages[$id]['body']);
            }
        }
    }

    return $messages;
}

?>
