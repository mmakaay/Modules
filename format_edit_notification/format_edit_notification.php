<?php

define("EDIT_NOTIFICATION_MARKER_PRE",  "###edit_notification_pre###");
define("EDIT_NOTIFICATION_MARKER_POST", "###edit_notification_post###");

// Defaults to use. If these are changed, remember to change them
// in settings.php as well.
if (!isset($GLOBALS['PHORUM']['mod_format_edit_notification']['prefix']) &&
    !isset($GLOBALS['PHORUM']['mod_format_edit_notification']['postfix'])) {
        $GLOBALS['PHORUM']['mod_format_edit_notification']['prefix'] =
            "<br />\n<br />\n<br />\n<small>";
        $GLOBALS['PHORUM']['mod_format_edit_notification']['postfix'] =
            '</small>';
}

function phorum_mod_format_edit_notification_common($data)
{
    global $PHORUM;

    $PHORUM["DATA"]["LANG"]["EditedMessage"] =
        EDIT_NOTIFICATION_MARKER_PRE .
        $PHORUM["DATA"]["LANG"]["EditedMessage"] .
        EDIT_NOTIFICATION_MARKER_POST;

    // Support for the readable dates module that uses an edit
    // message of its own for replacing the original one. Without this,
    // we would lose our special markers during this replacement.
    $PHORUM['DATA']['LANG']['mod_readable_dates']['edit_message'] =
        EDIT_NOTIFICATION_MARKER_PRE .
        $PHORUM['DATA']['LANG']['mod_readable_dates']['edit_message'] .
        EDIT_NOTIFICATION_MARKER_POST;

    return $data;
}

function phorum_mod_format_edit_notification_format($messages)
{
    $PHORUM = $GLOBALS["PHORUM"];

    foreach ($messages as $id => $message)
    {
        if (!isset($message["body"])) continue;

        $body = $message["body"];

        $body = preg_replace(
            '/(?:<phorum break>\n)*\Q'.EDIT_NOTIFICATION_MARKER_PRE.'\E/',
            #'/'.EDIT_NOTIFICATION_MARKER_PRE.'/',
            $PHORUM["mod_format_edit_notification"]["prefix"],
            $body
        );

        $body = str_replace(
            EDIT_NOTIFICATION_MARKER_POST,
            $PHORUM["mod_format_edit_notification"]["postfix"],
            $body
        );

        $messages[$id]["body"] = $body;
    }

    return $messages;
}

?>
