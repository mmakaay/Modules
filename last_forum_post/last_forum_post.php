<?php

// Initialize the tracking array if there's none available yet.
if (empty($GLOBALS['PHORUM']['mod_last_forum_post'])) {
    $GLOBALS['PHORUM']['mod_last_forum_post'] = array();
}

//------------------ HELPER FUNCTION FOR UPDATING CACHED INFO

// This function can be used to update the cached last message information
// for a given forum id. It will retrieve the most recent message from the
// database and put that information in the module's settings array.

function mod_last_forum_post_update($forum_id)
{
    global $PHORUM;
    settype($forum_id, 'int');

    // Check for bogus forum_id values.
    if ($forum_id > 0)
    {
        // Get the most recent message for the given forum id.
        $msgs = phorum_db_get_recent_messages(1, 0, $forum_id);
        unset($msgs['users']);

        // Check if we got a result back.
        if (!empty($msgs))
        {
            // Transfer the last message info to the module's settings array.
            list($last_id, $last_message) = each($msgs);
            $PHORUM['mod_last_forum_post'][$forum_id] = array(
                'author'     => $last_message['author'],
                'user_id'    => $last_message['user_id'],
                'subject'    => $last_message['subject'],
                'message_id' => $last_message['message_id'],
                'thread'     => $last_message['thread']
            );
        }
        // We got no result. We have to asume that there are no messages
        // available in the forum at all (e.g. all hidden or deleted).
        // Make sure that the entry for the forum_id is squashed.
        else {
            unset($PHORUM['mod_last_forum_post'][$forum_id]);
        }

        // Store the modified module settings array in the database.
        phorum_db_update_settings(array(
            'mod_last_forum_post' => $PHORUM['mod_last_forum_post']
        ));
    }
}

//------------------ HOOKS FOR ACTIONS THAT CAN AFFECT THE LAST POST DATA

function phorum_mod_last_forum_post_after_post($data)
{
    mod_last_forum_post_update($data['forum_id']);

    return $data;
}

function phorum_mod_last_forum_post_delete($data)
{
    mod_last_forum_post_update($GLOBALS['PHORUM']['forum_id']);

    return $data;
}

function phorum_mod_last_forum_post_move_thread($data)
{
    global $PHORUM;

    // Move from this forum ...
    mod_last_forum_post_update($GLOBALS['PHORUM']['forum_id']);

    // ... to this forum.
    $message = phorum_db_get_message($data, 'message_id', TRUE);
    if (!empty($message['forum_id'])) {
        mod_last_forum_post_update($message['forum_id']);
    }

    return $data;
}

function phorum_mod_last_forum_post_hide($data)
{
    $message = phorum_db_get_message($data, 'message_id', TRUE);
    if (!empty($message['forum_id'])) {
        mod_last_forum_post_update($message['forum_id']);
    }

    return $data;
}

function phorum_mod_last_forum_post_after_approve($data)
{
    mod_last_forum_post_update($data[0]['forum_id']);

    return $data;
}

//------------------ THE HOOK THAT MODIFIES THE INDEX PAGE DATA

function phorum_mod_last_forum_post_index($data)
{
    global $PHORUM;

    if (empty($PHORUM['mod_last_forum_post'])) return $data;
    $rfa = $PHORUM['mod_last_forum_post'];

    // We'll need this library to format the author and subject.
    require_once('./include/format_functions.php');

    // Function call argument for phorum_format_messages().
    $last_author_spec = array(
        "user_id",             // user_id
        "author",              // author
        NULL,                  // email (we won't link to email for recent)
        "last_author",         // target author field
        "last_author_profile"  // target author profile URL field
    );

    // Add the recent forum authors to the info.
    foreach ($data as $id => $forum)
    {
        $forum_id = $forum['forum_id'];

        // If no recent post info is available, NEXT FORUM!
        if (empty($rfa[$forum_id])) continue;

        $last = $rfa[$forum_id];

        // format messages
        $rows = phorum_format_messages(
            array($last['message_id'] => $last),
            array($last_author_spec)
        );
        $last = $rows[$last['message_id']];

        // Transfer formatted data to the $data array.
        if (!empty($last['last_author'])) {
            $data[$id]['last_author'] = $last['last_author'];
        }
        if (!empty($last['subject'])) {
            $data[$id]['last_subject'] = $last['subject'];
        }
        if (!empty($last['user_id'])) {
            $url = phorum_get_url(PHORUM_PROFILE_URL, $last['user_id']);
            $data[$id]['URL']['PROFILE'] = $last['URL']['last_author_profile'];
        }
        if (!empty($last['message_id'])) {
            $url = phorum_get_url(PHORUM_FOREIGN_READ_URL, $forum_id, $last['thread'], $last['message_id']);
            $data[$id]['URL']['MESSAGE'] = $url;
        }
    }

    return $data;
}

?>
