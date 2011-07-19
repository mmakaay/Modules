<?php

if (!defined('PHORUM')) return;

function phorum_mod_customize_mail_messages_email_user_start($data)
{
    $PHORUM = $GLOBALS['PHORUM'];

    // We're done if there are no overrides at all.
    if (empty($data[1]['language']) ||
        empty($PHORUM['mod_customize_mail_messages'][$data[1]['language']])) {
        return $data;
    }

    $overrides = $PHORUM['mod_customize_mail_messages'][$data[1]['language']];

    // Handle subject override.
    if (isset($data[1]['mailsubjecttpl']) &&
        isset($overrides[$data[1]['mailsubjecttpl']])) {
        $data[1]['mailsubject'] = $overrides[$data[1]['mailsubjecttpl']];
    }

    // Handle body override.
    if (isset($data[1]['mailmessagetpl']) &&
        isset($overrides[$data[1]['mailmessagetpl']])) {
        $data[1]['mailmessage'] = $overrides[$data[1]['mailmessagetpl']];
    }

    // Handle reply-to header override.
    if (isset($data[1]['mailmessagetpl']) &&
        isset($overrides[$data[1]['mailmessagetpl']."_mailfrom"])) {
        $mailfrom = $overrides[$data[1]['mailmessagetpl']."_mailfrom"];
        $data[1]['from_address'] = $mailfrom;
    }

    return $data;
}

?>
