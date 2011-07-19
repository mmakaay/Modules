<?php

if(!defined("PHORUM_ADMIN")) return;

// A list of user types for which we want to hide edit messages.
$mod_hide_edit_notification_usertypes = array(
    "admin"       => "administrators",
    "moderator"   => "moderators",
    "user"        => "users",
);

// save settings
if(count($_POST))
{
    $PHORUM["mod_hide_edit_notification"] = array();
    foreach ($mod_hide_edit_notification_usertypes as $id => $description) {
      $PHORUM["mod_hide_edit_notification"][$id] = isset($_POST[$id])?1:0;
    }

    if(!phorum_db_update_settings(array("mod_hide_edit_notification"=>$PHORUM["mod_hide_edit_notification"]))){
        phorum_admin_error("Database error while updating settings");
    } else {
        phorum_admin_okmsg("Settings Updated");
    }
}

include_once "./include/admin/PhorumInputForm.php";
$frm =& new PhorumInputForm ("", "post", "Save");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "hide_edit_notification");

$frm->addbreak("Edit settings for the Hide Editmessage module");

foreach ($mod_hide_edit_notification_usertypes as $id => $description)
{
    if (! isset($PHORUM["mod_hide_edit_notification"][$id])) {
        $PHORUM["mod_hide_edit_notification"][$id] = 0;
    }
    $frm->addrow("Hide edit message for $description", $frm->checkbox($id, "1", "", $PHORUM["mod_hide_edit_notification"][$id]));
}

$frm->show();

?>
