<?php

// Make sure that this script is loaded from the admin interface.
if(!defined("PHORUM_ADMIN")) return;

// Save settings in case this script is run after posting
// the settings form.
if(count($_POST))
{
    // Create the settings array for this module.
    $PHORUM["mod_allpagesread"] = array(
        "max_read_length"  => $_POST["max_read_length"] + 0,
        "enable_for"       => $_POST["enable_for"]
    );

    // Force the displaycount to be an integer value.
    settype($PHORUM["mod_allpagesread"]["max_read_length"], "int");

    if(! phorum_db_update_settings(array("mod_allpagesread"=>$PHORUM["mod_allpagesread"]))) {
        phorum_admin_error("A database error occured. The settings are not saved.");
    } else {
        phorum_admin_okmsg("Settings Updated");
    }
}

// Apply default values for the settings.
if (!isset($PHORUM["mod_allpagesread"]["max_read_length"])) $PHORUM["mod_allpagesread"]["max_read_length"] = 200;
if (!isset($PHORUM["mod_allpagesread"]["enable_for"])) $PHORUM["mod_allpagesread"]["enable_for"] = 0;

// Build the settings form.
include_once "./include/admin/PhorumInputForm.php";
$frm = new PhorumInputForm ("", "post", "Save");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "allpagesread");

$frm->addbreak("Edit settings for the all pages read module");
$frm->addrow("Enable this feature for", $frm->select_tag("enable_for", array(0 => "All visitors", 1 => "Only logged in users"), $PHORUM["mod_allpagesread"]["enable_for"]));
$frm->addrow("Max nr. of messages for which to show the All link (default: 200)", $frm->text_box('max_read_length', $PHORUM["mod_allpagesread"]["max_read_length"], 6));
$frm->show();

?>
