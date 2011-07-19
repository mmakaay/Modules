<?php

// Make sure that this script is loaded from the admin interface.
if(!defined("PHORUM_ADMIN")) return;

// Save settings in case this script is run after posting
// the settings form.
if(count($_POST))
{
    // Create the settings array for this module.
    $PHORUM["mod_meta_description"] = array(
        "excerpt_paragraphs"  => (int) $_POST["excerpt_paragraphs"],
        "excerpt_words"       => (int) $_POST["excerpt_words"],
        "excerpt_characters"  => (int) $_POST["excerpt_characters"],
    );

    if(! phorum_db_update_settings(array("mod_meta_description"=>$PHORUM["mod_meta_description"]))) {
        phorum_admin_error("A database error occured. The settings are not saved.");
    } else {
        phorum_admin_okmsg("Settings Updated");
    }
}

require_once("./mods/meta_description/defaults.php");

// Build the settings form.
include_once "./include/admin/PhorumInputForm.php";
$frm = new PhorumInputForm ("", "post", "Save");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "meta_description");

$frm->addbreak("Edit settings for the Meta Description Module");
$frm->addmessage("Below, you can configure the parameters that are used for creating an excerpt of a message body. The excerpt can be used as the meta description for the web page.<br/><br/>If you set some parameter's value to zero, then the parameter will not be used at all. Else, the body text will be trimmed to match the given value. If all parameters are set to zero, then the full message body will be used (not recommended). If multiple parameters are set, they will be handled in the order: paragraphs, words, characters.");

$frm->addrow("Maximum paragraphs", $frm->text_box('excerpt_paragraphs', $PHORUM["mod_meta_description"]["excerpt_paragraphs"], 6));
$frm->addrow("Maximum words", $frm->text_box('excerpt_words', $PHORUM["mod_meta_description"]["excerpt_words"], 6));
$frm->addrow("Maximum characters", $frm->text_box('excerpt_characters', $PHORUM["mod_meta_description"]["excerpt_characters"], 6));
$frm->show();

?>
