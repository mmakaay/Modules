<?php
    if (!defined("PHORUM_ADMIN")) return;

    // save settings
    if(count($_POST))
    {
        $PHORUM["mod_new_count"]["hide_totals"] =
            isset($_POST["hide_totals"]) ? 1 : 0;
        $PHORUM["mod_new_count"]["hide_ifnonew"] =
            isset($_POST["hide_ifnonew"]) ? 1 : 0;

        if (!phorum_db_update_settings(array(
            "mod_new_count" => $PHORUM["mod_new_count"]
        ))) {
            $error="Database error while updating settings.";
        } else {
            phorum_admin_okmsg("Settings updated");
        }
    }

    include_once "./include/admin/PhorumInputForm.php";
    $frm = new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "new_count");

    if (!empty($error)){
        phorum_admin_error($error);
    }

    $frm->addbreak("Edit settings for the new count module");

    $row = $frm->addrow("Do not display new count totals automatically", $frm->checkbox("hide_totals", "1", "Yes", $PHORUM["mod_new_count"]["hide_totals"]));
    $frm->addhelp($row, "Do not display new count totals automatically", "This option can be used to disable automatic displaying of the totals count (in the header of the forum index and message lists). This way, you can disable this feature or place the totals anywhere in the templates yourself (see this module's README for an explanation how to do this)");

    $row = $frm->addrow("Hide new count totals if no new messages are available", $frm->checkbox("hide_ifnonew", "1", "Yes", $PHORUM["mod_new_count"]["hide_ifnonew"]));

    $frm->show();
?>
