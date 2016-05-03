<?php

    if(!defined("PHORUM_ADMIN")) return;

    // save settings
    if(count($_POST)){
        $PHORUM["mod_bbcode_google"]["links_in_new_window"]=$_POST["links_in_new_window"] ? 1 : 0;
        $PHORUM["mod_bbcode_google"]["show_logo"]=$_POST["show_logo"] ? 1 : 0;
        $PHORUM["mod_bbcode_google"]["builtin_style"]=$_POST["builtin_style"] ? 1 : 0;
        $PHORUM["mod_bbcode_google"]["enable_editor_tool"]=$_POST["enable_editor_tool"] ? 1 : 0;

        if(!phorum_db_update_settings(array("mod_bbcode_google"=>$PHORUM["mod_bbcode_google"]))){
            $error="Database error while updating settings.";
        }
        else {
            phorum_admin_okmsg("Settings Updated");
        }
    }

    if (! isset($PHORUM["mod_bbcode_google"]["show_logo"])) {
        $PHORUM["mod_bbcode_google"]["show_logo"] = 1;
    }

    if (! isset($PHORUM["mod_bbcode_google"]["builtin_style"])) {
        $PHORUM["mod_bbcode_google"]["builtin_style"] = 1;
    }

    include_once "./include/admin/PhorumInputForm.php";
    $frm = new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "bbcode_google");

    if (!empty($error)){
        phorum_admin_error($error);
    }

    $frm->addbreak("Edit settings for the Google Links module");
    $frm->addrow("Open Google links in new window: ", $frm->checkbox("links_in_new_window", "1", "", $PHORUM["mod_bbcode_google"]["links_in_new_window"]));
    $row = $frm->addrow("Use the built-in styling for the Google links: ", $frm->checkbox("builtin_style", "1", "", $PHORUM["mod_bbcode_google"]["builtin_style"]));
    $frm->addhelp($row, "Use built-in styling", "This module has a built-in style for the created links. By disabling this option, you can create a fully customzed style. The Google links are incorporated in a span with the classname \"phorum_mod_bbcode_google\", so for customization you can create a CSS style definition in your Phorum templates.");
    $frm->addrow("Show the Google logo in the built-in style: ", $frm->checkbox("show_logo", "1", "", $PHORUM["mod_bbcode_google"]["show_logo"]));

    if (!empty($PHORUM['mods']['editor_tools'])) {
        $frm->addrow("Add a Google button to the Editor Tools", $frm->checkbox("enable_editor_tool", "1", "", $PHORUM["mod_bbcode_google"]["enable_editor_tool"]));
    }

    $frm->show();
?>
