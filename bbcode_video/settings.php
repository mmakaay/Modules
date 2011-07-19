<?php
    if(!defined("PHORUM_ADMIN")) return;

    if(count($_POST))
    {
        $PHORUM["mod_bbcode_video"] = array(
          "handle_plain_urls" => empty($_POST["handle_plain_urls"]) ? 0 : 1,
          "enable_editor_tool" => empty($_POST["enable_editor_tool"]) ? 0 : 1
        );

        if (!phorum_db_update_settings(array("mod_bbcode_video" => $PHORUM["mod_bbcode_video"]))) {
            phorum_admin_error("Database error while updating settings.");
        } else {
            phorum_admin_okmsg("The settings were successfully stored.");
        }
    }

    include_once "./include/admin/PhorumInputForm.php";
    $frm =& new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "bbcode_video");

    $frm->addbreak("Edit settings for the BBcode Video module");

    if (!empty($PHORUM['mods']['editor_tools'])) {
        $frm->addrow("Add a bbcode video button to the Editor Tools", $frm->checkbox("enable_editor_tool", "1", "", $PHORUM["mod_bbcode_video"]["enable_editor_tool"]));
    }

    $frm->addmessage(
        "When users post a link to a video web site to your forum,
         you can choose to automatically replace that link with
         an embedded video player, so the video is visible in your
         page as if it were added using the [video] bbcode tag.");
    $frm->addrow("Automatically show video links as embedded video", $frm->checkbox("handle_plain_urls", "1", "", $PHORUM["mod_bbcode_video"]["handle_plain_urls"]));

    $frm->show();
?>
