<?php
    if (count($_POST))
    {
        $settings =& $GLOBALS["PHORUM"]["mod_banner_manager"];

        $settings["hide_banner"] = $_POST["hide_banner"] ? 1 : 0;

        $settings["banner_position"] = $_POST["banner_position"];
        if (! preg_match('/^(after_header|before_footer)$/', $settings["banner_position"])) {
            $settings["banner_position"] = "after_header";
        }

        $settings["banner_alignment"] = $_POST["banner_alignment"];
        if (! preg_match('/^(left|center|right)$/', $settings["banner_alignment"])) {
            $settings["banner_alignment"] = "center";
        }

        banner_manager_save_settings();
        banner_manager_save_success();
    }
?>

    On this page, you can change some general settings for the module.
    For more information about the options, click on the question marks.
    <br/>
    <br/>

<?php
    include_once "./include/admin/PhorumInputForm.php";
    $frm = new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "banner_manager");
    $frm->hidden("panel", "settings");

    $row = $frm->addrow("Do not display the banner code automatically ", $frm->checkbox("hide_banner", "1", "", $PHORUM["mod_banner_manager"]["hide_banner"]));
    $frm->addhelp($row, "Do not display the banner code automatically", "This option can be used to disable automatic displaying of the banner. This way, you can place the banner anywhere in the templates by hand. Simply place the code {MOD_BANNER_MANAGER} in your templates at the place where you want the banner to appear.");

    $row = $frm->addrow("Banner position ", $frm->select_tag("banner_position",array("after_header" => "Top of page", "before_footer" => "Bottom of page"),$PHORUM['mod_banner_manager']['banner_position']));
    $frm->addhelp($row, "Banner position", "If you display the banner automatically (if you didn't enable \"Do not display the banner code automatically\"), this option controls at what position in the page the banner is displayed.");

    $row = $frm->addrow("Banner alignment ", $frm->select_tag("banner_alignment",array("left" => "To the left", "center" => "Centered", "right" => "To the right"),$PHORUM['mod_banner_manager']['banner_alignment']));
    $frm->addhelp($row, "Banner alignment", "If you display the banner automatically (if you didn't enable \"Do not display the banner code automatically\"), this option controls how the banner is aligned on the page.");

    $frm->show();
?>
