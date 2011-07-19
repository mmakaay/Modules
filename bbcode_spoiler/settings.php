<?php
    if(!defined("PHORUM_ADMIN")) return;

    if(count($_POST))
    {
        $PHORUM["mod_bbcode_spoiler"] = array(
          "enable_editor_tool" => empty($_POST["enable_editor_tool"]) ? 0 : 1,
          "allow_in_signature" => empty($_POST["allow_in_signature"]) ? 0 : 1,
        );

        phorum_db_update_settings(array(
            "mod_bbcode_spoiler" => $PHORUM["mod_bbcode_spoiler"]
        ));
        phorum_admin_okmsg("The settings were successfully stored.");
    }

    require_once('./include/admin/PhorumInputForm.php');
    $frm = new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "bbcode_spoiler");

    $frm->addbreak("Edit settings for the BBcode Spoiler module");

    if (!empty($PHORUM['mods']['editor_tools'])) {
        $frm->addrow("Add a bbcode spoiler button to the Editor Tools", $frm->checkbox("enable_editor_tool", "1", "", !empty($PHORUM["mod_bbcode_spoiler"]["enable_editor_tool"])));
    }

    $frm->addrow("Allow the use of spoilers in the signature", $frm->checkbox("allow_in_signature", "1", "", !empty($PHORUM["mod_bbcode_spoiler"]["allow_in_signature"])));

    $frm->show();
?>
