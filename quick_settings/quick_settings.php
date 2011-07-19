<?php

if (!defined("PHORUM")) return;

function phorum_mod_quick_settings_page_read()
{
    global $PHORUM;

    // The quick settings features are only available for
    // authenticated users and when display settings are not fixed.
    if (!$PHORUM['DATA']['LOGGEDIN'] || $PHORUM['display_fixed']) return;

    // Retrieve thread and message_id, which we need to build URLs.
    if (empty ($PHORUM["args"][2])) {
        $thread = (int) $PHORUM ["args"][1];
        $message_id = (int) $PHORUM["args"][1];
    } else {
        $thread = (int) $PHORUM["args"][1];
        $message_id = (int) $PHORUM["args"][2];
    }

    // When the set_viewmode parameter is in the request, then use it
    // to update the view mode for the active user.
    if (isset($PHORUM['args']['set_viewmode']))
    { 
        $mode = (int) $PHORUM['args']['set_viewmode'];
        if ($mode == PHORUM_THREADED_OFF ||
            $mode == PHORUM_THREADED_ON  ||
            $mode == PHORUM_THREADED_HYBRID)
        {
            phorum_api_user_save(array(
                'user_id'       => $PHORUM['user']['user_id'],
                'threaded_read' => $mode 
            ));

            // Redirect to have the GUI reflect the new display mode.
            $redir = phorum_get_url(PHORUM_READ_URL, $thread, $message_id);
            phorum_redirect_by_url($redir);
        }
    }

    // Setup template data for creating the quick setting URLs.  

    $url = phorum_get_url(
        PHORUM_READ_URL, $thread, $message_id, "set_viewmode=%m%");

    $lang = $PHORUM['DATA']['LANG'];

    $PHORUM['DATA']['MOD_QUICK_SETTINGS']['VIEWMODES'] = array(
        array(
            'URL'         => str_replace('%m%', PHORUM_THREADED_OFF, $url),
            'MODE'        => 'flat', 
            'DESCRIPTION' => $lang['ViewFlatRead'],
            'ACTIVE'      => $PHORUM['threaded_read'] == 0
        ),
        array(
            'URL'         => str_replace('%m%', PHORUM_THREADED_HYBRID, $url),
            'MODE'        => 'hybrid', 
            'DESCRIPTION' => $lang['ViewHybridRead'],
            'ACTIVE'      => $PHORUM['threaded_read'] == 2
        ),
        array(
            'URL'         => str_replace('%m%', PHORUM_THREADED_ON, $url),
            'MODE'        => 'threaded', 
            'DESCRIPTION' => $lang['ViewThreadedRead'],
            'ACTIVE'      => $PHORUM['threaded_read'] == 1
        )
    );
}

function phorum_mod_quick_settings_page_list()
{
    global $PHORUM;

    // The quick settings features are only available for
    // authenticated users and when display settings are not fixed.
    if (!$PHORUM['DATA']['LOGGEDIN'] || $PHORUM['display_fixed']) return;

    // When the set_viewmode parameter is in the request, then use it
    // to update the view mode for the active user.
    if (isset($PHORUM['args']['set_viewmode']))
    { 
        $mode = (int) $PHORUM['args']['set_viewmode'];
        if ($mode == PHORUM_THREADED_OFF ||
            $mode == PHORUM_THREADED_ON)
        {
            phorum_api_user_save(array(
                'user_id'       => $PHORUM['user']['user_id'],
                'threaded_list' => $mode 
            ));

            // Redirect to have the GUI reflect the new display mode.
            $redir = phorum_get_url(PHORUM_LIST_URL, $PHORUM['forum_id']);
            phorum_redirect_by_url($redir);
        }
    }

    // Setup template data for creating the quick setting URLs.  

    $url = phorum_get_url(
        PHORUM_LIST_URL, $PHORUM['forum_id'], "set_viewmode=%m%");

    $lang = $PHORUM['DATA']['LANG'];

    $PHORUM['DATA']['MOD_QUICK_SETTINGS']['VIEWMODES'] = array(
        array(
            'URL'         => str_replace('%m%', PHORUM_THREADED_OFF, $url),
            'MODE'        => 'flat', 
            'DESCRIPTION' => $lang['ViewFlatList'],
            'ACTIVE'      => $PHORUM['threaded_list'] == 0
        ),
        array(
            'URL'         => str_replace('%m%', PHORUM_THREADED_ON, $url),
            'MODE'        => 'threaded', 
            'DESCRIPTION' => $lang['ViewThreadedList'],
            'ACTIVE'      => $PHORUM['threaded_list'] == 1
        )
    );
}

?>
