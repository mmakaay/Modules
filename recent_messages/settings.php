<?php

if (!defined("PHORUM_ADMIN")) return;

require_once("./mods/recent_messages/defaults.php");

// save settings
if (count($_POST))
{
    $PHORUM["mod_recent_messages"]["enable_readable_dates"] =
        empty($_POST["enable_readable_dates"]) ? 0 : 1;

    $PHORUM["mod_recent_messages"]["group_by_readable_date"] =
        empty($_POST["group_by_readable_date"]) ? 0 : 1;

    $PHORUM["mod_recent_messages"]["enable_jumpmenu"] =
        empty($_POST["enable_jumpmenu"]) ? 0 : 1;

    $PHORUM["mod_recent_messages"]["enable_user_avatar"] =
        empty($_POST["enable_user_avatar"]) ? 0 : 1;

    phorum_db_update_settings(array(
        "mod_recent_messages" => $PHORUM["mod_recent_messages"]
    ));
    phorum_admin_okmsg("Settings updated");
}

include_once "./include/admin/PhorumInputForm.php";
$frm = new PhorumInputForm ("", "post", "Save settings");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "recent_messages");

$frm->addbreak("Edit settings for the Recent Messages module");

$warn = '';
if (!file_exists('./mods/readable_dates')) {
    $warn = '<div style="color:red">This module is currently not installed</div>';
} elseif (empty($PHORUM['mods']['readable_dates'])) {
    $warn = '<div style="color:red">This module is currently not enabled</div>';
}
$row = $frm->addrow("Enable date formatting by the Readable Dates module?$warn", $frm->checkbox("enable_readable_dates", "1", "Yes", $PHORUM["mod_recent_messages"]["enable_readable_dates"]));

$frm->addhelp($row, 'Enable date formatting by the Readable Dates module?',
    "The Readable Dates module makes dates easier to read for humans,
     by using relative indications like \"two hours ago\",
     \"yesterday, 12:30\", \"today, 17:52\", \"two weeks ago\",
     \"last year\", etc. If you have installed and enabled that module,
     then you can have the dates that are displayed by this module
     formatted by the Readable Dates module, by enabling this option."
);

$row = $frm->addrow("Enable Readable Dates for \"Group by date\"?$warn", $frm->checkbox("group_by_readable_date", "1", "Yes", $PHORUM["mod_recent_messages"]["group_by_readable_date"]));

$frm->addhelp($row, "Enable Readable Dates for \"Group by date\"?",
    "The Readable Dates module can also be used for formatting the date
     by which the \"Group by date\" option groups the messages / threads.
     If you enable this options, then terms like \"today\", \"yesterday\",
     \"two days ago\" will be used for the grouping.<br/>
     <br/>
     Note that the Readable Dates module will use bigger intervals for
     older messages, so grouping will then no longer be done by day.
     You will then see things like \"12 days ago\", \"13 days ago\",
     \"2 weeks ago\", \"3 weeks ago\", etc."
);

$warn = '';
if (!file_exists('./mods/jumpmenu')) {
    $warn = '<div style="color:red">This module is currently not installed</div>';
} elseif (empty($PHORUM['mods']['jumpmenu'])) {
    $warn = '<div style="color:red">This module is currently not enabled</div>';
}
$row = $frm->addrow("Add a menu item to the Jumpmenu module's menu?$warn", $frm->checkbox("enable_jumpmenu", "1", "", $PHORUM["mod_recent_messages"]["enable_jumpmenu"]));

$frm->addhelp($row, 'Add menu item to Jumpmenu?',
    "The Jumpmenu module adds a popup menu to Phorum, which can be used to
     quickly navigate to some forum. By enabling this option, an extra
     menu item \"Recent Messages\" will be added to the top level popup menu."
);

$warn = '';
if (!file_exists('./mods/user_avatar')) {
    $warn = '<div style="color:red">This module is currently not installed</div>';
} elseif (empty($PHORUM['mods']['user_avatar'])) {
    $warn = '<div style="color:red">This module is currently not enabled</div>';
} elseif (!file_exists('./mods/user_avatar/api.php')) {
    $warn = '<div style="color:red">User Avatar version 3.1.0 or higher is required (note: not available for Phorum 5.2)</div>';
}
$row = $frm->addrow("Enable support for the User Avatar module?$warn", $frm->checkbox("enable_user_avatar", "1", "", $PHORUM["mod_recent_messages"]["enable_user_avatar"]));

$frm->addhelp($row, 'Enable support for the User Avatar module?',
    "When this option is enabled, the User Avatar module will be called
     to add avatar information to the messages in the recent messages
     overview. The avatar image URL will be available for use in the
     module templates as {MESSAGES->MOD_USER_AVATAR} (or
     {MESSAGE->MOD_USER_AVATAR}, depending on the template)"
);

$frm->show();

?>
