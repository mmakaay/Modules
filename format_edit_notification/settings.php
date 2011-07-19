<?php
if (!defined("PHORUM_ADMIN")) return;

// Defaults to use. If these are changed, remember to change them
// in settings.php as well.
if (!isset($GLOBALS['PHORUM']['mod_format_edit_notification']['prefix']) &&
    !isset($GLOBALS['PHORUM']['mod_format_edit_notification']['postfix'])) {
        $GLOBALS['PHORUM']['mod_format_edit_notification']['prefix'] =
            "<br />\n<br />\n<br />\n<small>";
        $GLOBALS['PHORUM']['mod_format_edit_notification']['postfix'] =
            '</small>';
}

if (count($_POST))
{
    $PHORUM["mod_format_edit_notification"] = array(
        "prefix"  => $_POST["prefix"],
        "postfix" => $_POST["postfix"]
    );
    
    phorum_db_update_settings(array(
      "mod_format_edit_notification" => $PHORUM["mod_format_edit_notification"]
    ));
    phorum_admin_okmsg("Settings Updated");
}

include_once "./include/admin/PhorumInputForm.php";

$frm = new PhorumInputForm ("", "post", "Save");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "format_edit_notification");

$frm->addbreak("Settings for the Format Edit Notificaton module");

$frm->addrow("HTML code to show before the edit notification:<br/>" . $frm->textarea( "prefix", $PHORUM["mod_format_edit_notification"]["prefix"], 30, 5, "style='width: 100%'" ));
$frm->addrow("HTML code to show after the edit notification:<br/>" . $frm->textarea( "postfix", $PHORUM["mod_format_edit_notification"]["postfix"], 30, 5, "style='width: 100%'" ));

$frm->show();


?>
