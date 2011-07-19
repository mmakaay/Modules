<?php
if (!defined("PHORUM_ADMIN")) return;

// Defaults to use. If these are changed, remember to change them
// in settings.php as well.
if (!isset($GLOBALS['PHORUM']['mod_format_signatures']['prefix']) &&
    !isset($GLOBALS['PHORUM']['mod_format_signatures']['postfix'])) {
    $GLOBALS['PHORUM']['mod_format_signatures']['prefix'] = "<br/>\n<hr/>\n";
    $GLOBALS['PHORUM']['mod_format_signatures']['postfix'] = "";
}

if (count($_POST))
{
    $PHORUM["mod_format_signatures"] = array(
        "prefix"  => $_POST["prefix"],
        "postfix" => $_POST["postfix"]
    );

    phorum_db_update_settings(array(
      "mod_format_signatures" => $PHORUM["mod_format_signatures"]
    ));
    phorum_admin_okmsg("Settings Updated");
}

include_once "./include/admin/PhorumInputForm.php";

$frm = new PhorumInputForm ("", "post", "Save");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "format_signatures");

$frm->addbreak("Settings for the Format Signatures module");

$frm->addrow("HTML code to show before the signatures:<br/>" . $frm->textarea( "prefix", $PHORUM["mod_format_signatures"]["prefix"], 30, 5, "style='width: 100%'" ));
$frm->addrow("HTML code to show after the signatures:<br/>" . $frm->textarea( "postfix", $PHORUM["mod_format_signatures"]["postfix"], 30, 5, "style='width: 100%'" ));

$frm->show();

?>
