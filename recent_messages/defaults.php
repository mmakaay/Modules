<?php
// A simple helper script that will setup initial module
// settings in case one of these settings is missing.

if(!defined("PHORUM") && !defined("PHORUM_ADMIN")) return;

// The settings array itself.
if (! isset($GLOBALS["PHORUM"]['mod_recent_messages'])) {
    $GLOBALS["PHORUM"]['mod_recent_messages'] = array();
}

$phorum_mod_recent_messages_defaults = array(
    'enable_readable_dates'  => 1,
    'group_by_readable_date' => 0,
    'enable_jumpmenu'        => 1,
    'enable_user_avatar'     => 0
);

// The default options.
foreach ($phorum_mod_recent_messages_defaults as $key => $val) {
    if (! isset($GLOBALS["PHORUM"]['mod_recent_messages'][$key])) {
        $GLOBALS["PHORUM"]['mod_recent_messages'][$key] = $val;
    }
}

?>
