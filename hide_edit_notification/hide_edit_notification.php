<?php

if (!defined('PHORUM')) return;

define('HIDE_EDIT_NOTIFICATION_MARKER_PRE',  '###h_edit_notification_pre###');
define('HIDE_EDIT_NOTIFICATION_MARKER_POST', '###h_edit_notification_post###');

function phorum_mod_hide_edit_notification_common()
{
    global $PHORUM;

    $PHORUM['DATA']['LANG']['EditedMessage'] =
        HIDE_EDIT_NOTIFICATION_MARKER_PRE .
        $GLOBALS['PHORUM']['DATA']['LANG']['EditedMessage'] .
        HIDE_EDIT_NOTIFICATION_MARKER_POST;
}

function phorum_mod_hide_edit_notification_read($messages)
{
    global $PHORUM;

    foreach ($messages as $id => $message)
    {
        // Nothing to do for messages without a body or with an empty counter.
        if (empty($message['meta']['edit_count']) ||
            !isset($message['body'])) continue;

        // See if we have to hide edit info for the current message. 
        $hideit = FALSE;
        if (!empty($PHORUM['mod_hide_edit_notification']['user'])) {
            $hideit = TRUE;
        }
        elseif (!empty($message['moderator_post']) &&
                !empty($PHORUM['mod_hide_edit_notification']['moderator'])) {
            $hideit = TRUE;
        }
        elseif (!empty($message['user']['admin']) &&
                !empty($PHORUM['mod_hide_edit_notification']['admin'])) {
            $hideit = TRUE;
        }

        // Clear the edit message.
        if ($hideit)
        {
            $messages[$id]['body'] = preg_replace(
                '/\n*' .
                '\Q'.HIDE_EDIT_NOTIFICATION_MARKER_PRE.'\E.*' .
                '\Q'.HIDE_EDIT_NOTIFICATION_MARKER_POST.'\E/m',
                "\n", $message['body']
            );

            // Prevent other modules that possibly handle edit
            // notifications from seeing the edit count.
            $messages[$id]['meta']['edit_count'] = 0;
        }
        // Keep the edit message. Only clean up the magic markers.
        else
        {
            $messages[$id]['body'] = str_replace(
                array(
                    HIDE_EDIT_NOTIFICATION_MARKER_PRE,
                    HIDE_EDIT_NOTIFICATION_MARKER_POST
                ),
                array(
                    '', ''
                ),
                $message['body']
            );
        }
    }

    return $messages;
}

?>
