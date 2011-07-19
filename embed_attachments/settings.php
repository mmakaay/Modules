<?php
if (!defined("PHORUM_ADMIN")) return;

// save settings
if (count($_POST))
{
    $PHORUM["mod_embed_attachments"]["auto_add_link"] =
        empty($_POST["auto_add_link"]) ? 0 : 1;

    phorum_db_update_settings(array(
        "mod_embed_attachments" => $PHORUM["mod_embed_attachments"]
    ));
    phorum_admin_okmsg("The settings were successfully saved.");
}

include_once "./include/admin/PhorumInputForm.php";
$frm = new PhorumInputForm ("", "post", "Save");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "embed_attachments");

$frm->addbreak("Edit settings for the Embed Attachments module");

$row = $frm->addrow("Automatically add attachment links to the body", $frm->checkbox("auto_add_link", "1", "Yes", $PHORUM["mod_embed_attachments"]["auto_add_link"]));
$frm->addhelp($row, "Automatically add attachment links to the body", "The module can automatically add an attachment link to the message body when a new attachment is uploaded, so the user won't have to add it manually.");

$frm->show();

?>
