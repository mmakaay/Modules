<?php

if (!defined("PHORUM")) return;

// Handle thread limiting for the read page
function phorum_mod_limit_threaded_views_page_read()
{
    global $PHORUM;

    // Check if we have a thread id in the request.
    if (!isset($PHORUM['args'][1]) || !is_numeric($PHORUM['args'][1]))
        return;

    // No action needed if we're in flat mode already.
    if (!$PHORUM['threaded_read']) return;

    // Check if a limit is configured for the current view mode.
    $setting = $PHORUM['threaded_read'] == 2
             ? 'read_hybrid' : 'read_threaded';
    if (empty($PHORUM['mod_limit_threaded_views'][$setting])) return;
    $limit = $PHORUM['mod_limit_threaded_views'][$setting];

    // Find out how many messages there are in the current thread.
    $thread_id = (int) $PHORUM['args'][1];
    $thread = phorum_db_get_message($thread_id, 'message_id');
    if ($thread && !empty($thread['thread_count']) &&
        $thread['thread_count'] > $limit) {
        $PHORUM['threaded_read'] = 0;
        $PHORUM['mod_limit_threaded_views']['limited'] = 1;
    }
}

// Handle thread limiting for the list page
function phorum_mod_limit_threaded_views_page_list()
{
    global $PHORUM;

    // Check if we have a forum id in the request.
    if (!isset($PHORUM['args'][0]) || !is_numeric($PHORUM['args'][0]))
        return;

    // No action needed if we're in flat mode already.
    if (!$PHORUM['threaded_list']) return;

    // Check if a limit is configured for the current view mode.
    if (empty($PHORUM['mod_limit_threaded_views']['list_threaded']))
        return;
    $limit = $PHORUM['mod_limit_threaded_views']['list_threaded'];

    // Find out how many messages will be shown on the list page.
    // We temporarily fake flat read mode for now, using the
    // threaded mode's setting for the threads per page.
    $orig = $PHORUM["threaded_list"];
    $PHORUM["list_length"] = $PHORUM['list_length_threaded'];
    $PHORUM['threaded_list'] = 0;

    // Figure out what page we are on.
    if (empty($PHORUM["args"]["page"]) ||
        !is_numeric($PHORUM["args"]["page"]) ||
        $PHORUM["args"]["page"] < 0) {
        $page = 1;
    } else {
        $page = (int) $PHORUM["args"]["page"];
    }
    $offset = $page - 1;

    // Get the visible threads for the current page.
    $threads = phorum_db_get_thread_list($offset);

    // Compute the number of messages.
    $count = 0;
    foreach ($threads as $thread) {
        if (!empty($thread['thread_count'])) {
            $count += $thread['thread_count'];
        }
    }

    // Switch to flat mode if needed.
    if ($count > $limit) {
        $PHORUM['threaded_list'] = 0;
        $PHORUM['list_length'] = $PHORUM['list_length_flat'];
        $PHORUM['mod_limit_threaded_views']['limited'] = 1;
    } else {
        $PHORUM['threaded_list'] = $orig;
    }
}

function phorum_mod_limit_threaded_views_after_header()
{
    global $PHORUM;

    if (!empty($PHORUM['mod_limit_threaded_views']['limited']) &&
        !empty($PHORUM['mod_limit_threaded_views']['show_notice'])) {
        include(phorum_get_template('limit_threaded_views::notification'));
    }
}

?>
