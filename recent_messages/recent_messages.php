<?php

define('NO_GROUPING',     0);
define('GROUP_BY_DATE',   1);
define('GROUP_BY_FORUM',  2);
define('GROUP_BY_FOLDER', 3);

require_once './mods/recent_messages/defaults.php';
require_once './include/api/newflags.php';

function phorum_mod_recent_messages_start_output()
{
    global $PHORUM;

    $backup_id = $PHORUM['forum_id'];
    $PHORUM['forum_id'] = 0;
    $PHORUM['DATA']['URL']['RECENT_MESSAGES'] =
         phorum_get_url(PHORUM_ADDON_URL, 'module=recent_messages');
    $PHORUM['forum_id'] = $backup_id;
}

function recent_messages_getparam($name, $default = NULL, $is_int = TRUE)
{
    global $PHORUM;

    // Parameter processing for ajax requests.
    // Use the ajax parameter when available. If not, then check if
    // the user has the requested value in his settings. If all checks
    // fail, then fallback to the provided default.
    if (phorum_page == 'ajax')
    {
        $ajax_value = phorum_ajax_getarg($name, NULL, '');

        if ($ajax_value !== '' && $ajax_value !== NULL) {
            $value = $ajax_value;
        }
        else
        {
            if (isset($PHORUM['user']['mod_recent_messages'][$name])) {
                $value = $PHORUM['user']['mod_recent_messages'][$name];
            } else {
                $value = $default;
            }
        }
    }
    // Parameter processing for web requests.
    else
    {
        if (isset($_POST[$name])) {
            $value = $_POST[$name];
        } elseif (isset($_GET[$name])) {
            $value = $_GET[$name];
        } elseif (isset($PHORUM['args'][$name])) {
            $value = $PHORUM['args'][$name];
        } elseif (isset($PHORUM['user']['mod_recent_messages'][$name])) {
            $value = $PHORUM['user']['mod_recent_messages'][$name];
        } else {
            $value = $default;
        }
    }

    if ($is_int && $value !== NULL) settype($value, "int");

    return $value;
}

// This is a helper function that is used by both the standard page
// output and the ajax handler, to setup template data.
function mod_recent_messages_setup_templatedata()
{
    global $PHORUM;

    if (file_exists('./include/format_functions.php')) {
        require_once('./include/format_functions.php');
        require_once('./include/forum_functions.php');
    }

    // Check if the user has read access for the active forum.
    // Not really important to check, but good for some paranoia.
    if (!phorum_check_read_common()) { return; }

    // Load the module installation code if this was not yet done.
    // The installation code will take care of automatically adding
    // the custom profile field that is needed for this module.
    if (empty($GLOBALS['PHORUM']["mod_recent_messages"]["installed"])) {
        include("./mods/recent_messages/install.php");
    }

    // Retrieve displaying options.
    $show_amount  = recent_messages_getparam('show_amount', 10);
    $view_type    = recent_messages_getparam('view_type', 0);
    $show_forum   = recent_messages_getparam('show_forum', 0);
    $group_by     = recent_messages_getparam('group_by', GROUP_BY_DATE);
    $page         = recent_messages_getparam('page', 1);

    // Sanitize parameters, against funny users.
    if ($show_amount > 1000) $show_amount = 1000;
    if ($show_amount < 1)    $show_amount = 1;
    if ($page < 1) $page = 1;

    // Store displaying options for registered users, so the next time
    // they visit the page, the same options will be used.
    // Storing the options is not done when handling an Ajax request.
    // We allow the Ajax code to use independent options.
    if (phorum_page !== 'ajax' && !empty($PHORUM['user']['user_id'])) {
        phorum_api_user_save(array(
            'user_id' => $PHORUM['user']['user_id'],
            'mod_recent_messages' => array(
                'view_type'    => $view_type,
                'show_amount'  => $show_amount,
                'show_forum'   => $show_forum,
                'group_by'     => $group_by
            )
        ));
    }

    // Build all our basic URLs.
    phorum_build_common_urls();

    // Create a list of all available forums.
    // Forum API was renamed from 5.2 -> 5.3.
    require_once './include/api/forums.php';
    $available_forums = phorum_api_forums_by_vroot($PHORUM['vroot']);

    // Filter empty folders from the list.
    $count = array();
    foreach ($available_forums as $id => $forum) {
        if (isset($count[$forum['parent_id']])) {
            $count[$forum['parent_id']] ++;
        } else {
            $count[$forum['parent_id']] = 0;
        }
    }
    foreach ($available_forums as $id => $forum) {
        if ($forum['folder_flag'] && empty($count[$id])) {
            unset($available_forums[$id]);
        }
    }

    // Retrieve the recent messages from the database.
    $messages = phorum_db_get_recent_messages(
        $show_amount + 1,         // nr to retreive, +1 to check for more pages
        ($page-1) * $show_amount, // retrieval offset (paging)
        $show_forum,              // forum id
        0,                        // thread id
        $view_type                // what kind of overview to show
    );
    unset($messages['users']);

    // Handle paging setup.
    $prev_page = NULL;
    $next_page = NULL;
    if (count($messages) > $show_amount) {
        array_pop($messages);
        $next_page = $page + 1;
    }
    if ($page > 0) {
        $prev_page = $page - 1;
    }

    // Loop through al messages and prepare their display data.
    // Keep track of all forums for which messages are found.
    $messages_per_folder = array();
    $folders = array();
    $forums = array();
    foreach ($messages as $id => $message)
    {
        // The date to use, depends on the view mode.
        $date = $view_type == LIST_UPDATED_THREADS
              ? $message['modifystamp']
              : $message['datestamp'];

        // Date formatting, incase the Readable Dates module support is enabled.
        if (!empty($PHORUM['hooks']['format_readable_date']) &&
            !empty($PHORUM['mod_recent_messages']['enable_readable_dates']))
        {
            $messages[$id]['datestamp'] =
                phorum_hook('format_readable_date', $message['datestamp']);
            $messages[$id]['date'] =
                phorum_hook('format_readable_date', $date);
            $messages[$id]['time'] =
                phorum_hook('format_readable_date', $date);

            // Modifystamp formatting for thread starter messages.
            if ($message['parent_id'] == 0) {
                $messages[$id]['lastpost'] = phorum_hook(
                    'format_readable_date',
                    $message["modifystamp"]
                );
            }
        }
        // Standard Phorum date formatting.
        else
        {
            $messages[$id]['datestamp'] =
                phorum_date($PHORUM['short_date_time'], $message['datestamp']);

            // Modifystamp formatting for thread starter messages.
            if ($message['parent_id'] == 0) {
                $messages[$id]['lastpost'] = phorum_date(
                    $PHORUM["short_date_time"],
                    $message["modifystamp"]
                );
            }
        }

        // These are always formatted using the standard Phorum date formatting.
        $messages[$id]['date'] =
            phorum_date($PHORUM['short_date'], $date);
        $messages[$id]['time'] =
            phorum_date($PHORUM['short_time'], $date);

        // Format the date that we use for grouping.
        // If enabled in the settings, then use the readable date
        // for date grouping.
        if (!empty($PHORUM['hooks']['format_readable_date']) &&
            !empty($PHORUM['mod_recent_messages']['group_by_readable_date']))
        {
            // For grouping, we need a granularity of one day.
            // Also, strip a possible time reference from the date.
            // (e.g. "Today, 10:12:44")
            $orig = $PHORUM['mod_readable_dates']['granularity'];
            $PHORUM['mod_readable_dates']['granularity'] = 86400;
            $messages[$id]['group_date'] =
                preg_replace(
                    '/,?\s+[\d\:\.]+(\s*am|pm)?\s*$/i', '', 
                    phorum_hook('format_readable_date', $date)
                );
            $PHORUM['mod_readable_dates']['granularity'] = $orig;
        }
        // Otherwise, we use the plain date.
        else {
            $messages[$id]['group_date'] = $messages[$id]['date'];
        }

        $messages[$id]["URL"]["READ"] = phorum_get_url(
            PHORUM_FOREIGN_READ_URL,
            $message['forum_id'],
            $message["thread"],
            $message["message_id"]
        );

        // URLs for thread based views.
        if ($view_type == LIST_UPDATED_THREADS ||
	    $view_type == LIST_RECENT_THREADS)
	{
            $messages[$id]["URL"]["NEWPOST"] = phorum_get_url(
                PHORUM_FOREIGN_READ_URL,
                $message['forum_id'],
                $message['thread'],
                "gotonewpost"
            );
            $messages[$id]["URL"]["LAST_POST"] = phorum_get_url(
                PHORUM_FOREIGN_READ_URL,
                $message['forum_id'],
                $message['thread'],
                $message['recent_message_id']
            );
        }
	// URLs for mesage based views.
	else {
            $messages[$id]["URL"]["NEWPOST"] = phorum_get_url(
                PHORUM_FOREIGN_READ_URL,
                $message['forum_id'],
                $message['thread'],
                $message['message_id']
            );
        }

        // Keep track of used forums. This is a simple id => id mapping.
        $forums[$message['forum_id']] = $message['forum_id'];

        // Keep track of the folders / forums in which the messages
        // belong. This is used for grouping messages per folder or forum.
        // Historically, the involved variables are named after folders,
        // but remember that they can be forums too, based on the
        // $group_by parameter.

        // TODO: quick hack for suppressing errors for messages
        // from hidden forums. Still need to check if this is the right
        // solution here. I don't think this should happen with the new
        // 5.2 core code anymore.
        if (empty($available_forums[$message['forum_id']])) continue;

        $forum = $available_forums[$message['forum_id']];
        $path = $forum['forum_path'];
        if ($group_by == GROUP_BY_FOLDER) array_pop($path);
        $folder_id = $group_by == GROUP_BY_FOLDER
                   ? $forum['parent_id']
                   : $forum['forum_id'];
        $messages_per_folder[$folder_id][$id] = NULL;

        if (empty($folders[$folder_id])) {
            array_shift($path);
            if (empty($path)) $path = array($PHORUM['DATA']['LANG']['Forums']);
            $folders[$folder_id]['path_name'] = join(" &#187; ", $path);
        }
    }

    // The list page needs additional formatting for the recent author data
    $recent_author_spec = array(
        "recent_user_id",        // user_id
        "recent_author",         // author
        NULL,                    // email (we won't link to email for recent)
        "recent_author",         // target author field
        "RECENT_AUTHOR_PROFILE"  // target author profile URL field
    );

    // This will handle message formatting to format the subject line.
    $messages = phorum_format_messages($messages, array($recent_author_spec));

    // Apply User Avatar data.
    if (!empty($PHORUM['mod_recent_messages']['enable_user_avatar'])) {
        if (function_exists('mod_user_avatar_apply_avatar_to_messages')) {
            $messages = mod_user_avatar_apply_avatar_to_messages($messages);
        }
    }

    // Go over the messages again for adding the forum name and link.
    foreach($messages as $id => $row)
    {
        $forum_id = $row['forum_id'];

        $messages[$id]["URL"]["FORUM"] =
            phorum_get_url(PHORUM_LIST_URL, $forum_id);

        $messages[$id]["forum_name"] =
            $available_forums[$forum_id]["name"];
    }

    // Add newflags to the messages.
    $mode = ($view_type == LIST_UPDATED_THREADS ||
	     $view_type == LIST_RECENT_THREADS)
          ? PHORUM_NEWFLAGS_BY_THREAD
	  : PHORUM_NEWFLAGS_BY_MESSAGE;
    $messages = phorum_api_newflags_format_messages($messages, $mode);

    // ----------------------------------------------------------------------
    // Setup template data.
    // ----------------------------------------------------------------------

    // Messages per folder.
    foreach ($folders as $id => $folder)
    {
        // Fill the placeholder messages slots with real messages.
        foreach ($messages_per_folder[$id] as $mid => $dummy) {
            $messages_per_folder[$id][$mid] = $messages[$mid];
        }
        $folders[$id]['MESSAGES'] = $messages_per_folder[$id];
    }
    $PHORUM["DATA"]["FOLDERS"] = $folders;

    // Messages as a plain list.
    $PHORUM["DATA"]["MESSAGES"] = $messages;

    if (count($messages) == 0) {
        $PHORUM["DATA"]["NO_MESSAGES"] = $PHORUM["DATA"]["LANG"]["NoResults"];
    } else {
        $PHORUM["DATA"]["NO_MESSAGES"] = NULL;
    }

    $PHORUM["DATA"]["FORUMS"] = phorum_build_forum_list();

    $PHORUM["DATA"]["URL"]["NEXT_PAGE"] =
        $next_page
        ? phorum_get_url(
              PHORUM_ADDON_URL,
              "module=recent_messages",
              "page=$next_page",
              "show_forum=$show_forum",
              "show_amount=$show_amount",
              "view_type=$view_type",
              "group_by=$group_by"
          )
        : NULL;

    $PHORUM["DATA"]["URL"]["PREV_PAGE"] =
        $prev_page
        ? phorum_get_url(
              PHORUM_ADDON_URL,
              "module=recent_messages",
              "page=$prev_page",
              "show_forum=$show_forum",
              "show_amount=$show_amount",
              "view_type=$view_type",
              "group_by=$group_by"
          )
        : NULL;

    // PAGE & CURRENT_PAGE are both setup.
    // PAGE is for backward compatibility.
    $PHORUM["DATA"]["PAGE"] = $PHORUM["DATA"]["CURRENT_PAGE"] = $page;

    $PHORUM["DATA"]["URL"]["RECENT_MESSAGES"] =
        phorum_get_url(PHORUM_ADDON_URL, "module=recent_messages");

    $PHORUM["DATA"]["SHOW_AMOUNT"] = $show_amount;
    $PHORUM["DATA"]["VIEW_TYPE"]   = $view_type;
    $PHORUM["DATA"]["SHOW_FORUM"]  = $show_forum;
    $PHORUM["DATA"]["GROUP_BY"]    = $group_by;

    $PHORUM["DATA"]["POST_VARS"] .=
        '<input type="hidden" name="module" value="recent_messages"/>';

    // Listing unread messages can only be done for authenticated users
    // and for versions of Phorum that provide db backend support for it.
    if (!empty($PHORUM['user']['user_id']) &&
        defined('LIST_UNREAD_MESSAGES')) {
        $PHORUM['DATA']['ALLOW_UNREAD_MESSAGES'] = TRUE;
    }

    // Force the templates into showing the recent messages page
    // in the same way as the search page. Since the pages for this
    // module are some sort of search result, this is appropriate.
    $PHORUM['DATA']['PHORUM_PAGE'] = 'search';

    // Override the default title and description.
    $PHORUM['DATA']['HEADING'] =
        $PHORUM['DATA']['LANG']['mod_recent_messages']['RecentMessages'];
    $PHORUM['DATA']['HTML_TITLE'] =
        htmlspecialchars(strip_tags($PHORUM['DATA']['HEADING']));
    $PHORUM['DATA']['HTML_DESCRIPTION'] = '';

    $PHORUM['DATA']['BREADCRUMBS'][] = array(
        'URL'  => $PHORUM['DATA']['URL']['RECENT_MESSAGES'],
        'TEXT' => $PHORUM['DATA']['HEADING'],
        'TYPE' => 'recent_messages'
    );
}

function phorum_mod_recent_messages_addon()
{
    global $PHORUM;
    mod_recent_messages_setup_templatedata();
    phorum_output("recent_messages::page");
}

function phorum_mod_recent_messages_css_register($data)
{
    if ($data['css'] != 'css') return $data;

    $data['register'][] = array(
        "module" => "recent_messages",
        "where"  => "after",
        "source" => "template(recent_messages::css)"
    );

    return $data;
}

function phorum_mod_recent_messages_jumpmenu_add($items)
{
    global $PHORUM;

    if (!empty($PHORUM['mod_recent_messages']['enable_jumpmenu']))
    {
        $items[] = array(
            'name'  => $PHORUM['DATA']['LANG']['mod_recent_messages']['RecentMessages'],
            'url'   => phorum_get_url(PHORUM_ADDON_URL, "module=recent_messages"),
            'class' => 'recent_messages'
        );
    }

    return $items;
}

function phorum_mod_recent_messages_ajax()
{
    global $PHORUM;

    // Allow the use of a "content_only" parameter in the ajax call,
    // to flag the template that we only want the inner content for
    // the result and not the header and footer code.
    $content_only = phorum_ajax_getarg("content_only", NULL, FALSE);
    $PHORUM['DATA']['AJAX_CONTENT_ONLY'] =
      $content_only && $content_only !== 'false';

    // Allow the use of a "template" parameter in the ajax call,
    // to override the template to use for building the ajax response.
    $template = basename(phorum_ajax_getarg("template", NULL, ''));
    if ($template !== '') {
        $PHORUM['template'] = $template;
    }

    // Do not let the template variable end up in the URLs that are
    // generated for the return data.
    unset($_GET['template']);
    $vars =& $PHORUM['DATA']['GET_VARS'];
    foreach ($vars as $id => $var) {
        if (preg_match('/^template=/', $var)) {
            unset($PHORUM['DATA']['GET_VARS'][$id]);
            break;
        }
    }

    mod_recent_messages_setup_templatedata();

    ob_start();
    include phorum_get_template("recent_messages::page");
    $output = ob_get_clean();

    phorum_ajax_return($output);
}

?>
