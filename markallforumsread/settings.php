<?php
    if (!defined("PHORUM_ADMIN")) return;
    require_once("./mods/markallforumsread/defaults.php");

    // save settings
    if(count($_POST))
    {
        $PHORUM["mod_markallforumsread"]["show_only_if_new"] = 
            isset($_POST["show_only_if_new"]) ? 1 : 0;
        $PHORUM["mod_markallforumsread"]["show_after_header"] =
            isset($_POST["show_after_header"]) ? 1 : 0;
        $PHORUM["mod_markallforumsread"]["show_before_footer"] = 
            isset($_POST["show_before_footer"]) ? 1 : 0;

        if (!phorum_db_update_settings(array(
            "mod_markallforumsread" => $PHORUM["mod_markallforumsread"]
        ))) {
            phorum_admin_error("An error occurred while updating the settings in the database.");
        } else {
            phorum_admin_okmsg("Settings updated successfully.");
        }
    }

    include_once "./include/admin/PhorumInputForm.php";
    $frm =& new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "markallforumsread"); 

    $frm->addbreak("Edit settings for the Mark All Forums Read module");

    $frm->addrow("Only show the mark read link if there are new messages", $frm->checkbox("show_only_if_new", "1", "", $PHORUM["mod_markallforumsread"]["show_only_if_new"]));

    $frm->addmessage("
        Using the options below, you can automatically display the link
        for marking all forums read in the page. If you want to create
        your own links by editing the templates, then you can disable
        these options and make use of the following template variables:
        <br/><br/>
        {MARKALLFORUMSREAD_LINK} the full formatted link<br/>
        {MARKALLFORUMSREAD_URL} the url for marking the forums read<br/>
        {MARKALLFORUMSREAD_NEWCOUNT} the number of new messages available</br>
        <br/>
        See the README that came with this module for more information
        on this.
    ");

    $frm->addrow("Show link automatically after the header", $frm->checkbox("show_after_header", "1", "", $PHORUM["mod_markallforumsread"]["show_after_header"]));
    $frm->addrow("Show link automatically before the footer", $frm->checkbox("show_before_footer", "1", "", $PHORUM["mod_markallforumsread"]["show_before_footer"]));

    $frm->show();
?>
