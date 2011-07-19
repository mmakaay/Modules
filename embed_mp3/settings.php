<?php

    if(!defined("PHORUM_ADMIN")) return;

    // Apply default setting.
    if (!isset($PHORUM["mod_embed_mp3"]["enable_editor_tool"])) {
        $PHORUM["mod_embed_mp3"]["enable_editor_tool"] = 0;
    }
    if (!isset($PHORUM["mod_embed_mp3"]["embed_all_attachments"])) {
        $PHORUM["mod_embed_mp3"]["embed_all_attachments"] = 0;
    }

    // save settings
    if (count($_POST))
    {
        $PHORUM["mod_embed_mp3"]["enable_editor_tool"] =
            isset($_POST["enable_editor_tool"]) ? 1 : 0;

        $PHORUM["mod_embed_mp3"]["embed_all_attachments"] =
            isset($_POST["embed_all_attachments"]) ? 1 : 0;

        phorum_db_update_settings(array(
            "mod_embed_mp3" => $PHORUM["mod_embed_mp3"]
        ));
        phorum_admin_okmsg("Settings Updated");
    }

    include_once "./include/admin/PhorumInputForm.php";
    $frm = new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "embed_mp3");

    $frm->addbreak("Edit settings for the Embed MP3 module");

    $frm->addrow("Add an MP3 button to the Editor Tools?" . (empty($PHORUM['mods']['editor_tools']) ? "<br/>The editor tools module must be enabled for this." : ''), $frm->checkbox("enable_editor_tool", "1", "Yes", $PHORUM["mod_embed_mp3"]["enable_editor_tool"]));

    $frm->addrow("Show all attachments using the embedded MP3 player?", $frm->checkbox("embed_all_attachments", "1", "Yes", $PHORUM["mod_embed_mp3"]["embed_all_attachments"]));

    $frm->show();
?>
