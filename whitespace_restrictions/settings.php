<?php
if (!defined("PHORUM_ADMIN")) return;

require_once "./mods/whitespace_restrictions/defaults.php";

// Save module settings to the database.
if (count($_POST))
{
    // Build new settings array.
    $settings = array();
    $settings["limit"] = (int) $_POST["limit"];

    // Apply sanity.
    if ($settings["limit"] < 1) $settings["limit"] = 1;

    // Save settings array.
    $PHORUM["mod_whitespace_restrictions"] = $settings;
    phorum_db_update_settings(array(
        "mod_whitespace_restrictions" => $settings
    ));
    phorum_admin_okmsg("The module settings were successfully saved.");
}

require_once('./include/admin/PhorumInputForm.php');
$frm = new PhorumInputForm ("", "post", "Save");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "whitespace_restrictions"); 

$frm->addbreak("Edit settings for the whitespace restrictions module");

$row = $frm->addrow(
    "Maximum allowed number of lines in a row, containing only whitespace",
    $frm->text_box('limit', $PHORUM["mod_whitespace_restrictions"]["limit"], 6)
);
$frm->addhelp($row,
    "Maximum allowed number of lines in a row, containing only whitespace",
    "When a user posts a message that contains more blank lines in a row
     than the maximum that is configured here, the extraneous blank lines
     will be trimmed."
);

$frm->show();
?>
