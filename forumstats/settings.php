<?php

include_once('./mods/forumstats/defaults.php');

/* mod_forumstats: shows a couple of stats for a forum or globally
   author: Thomas Seifert (thomas@phorum.org)
*/
if(!defined("PHORUM_ADMIN")) return;

// save settings
if(count($_POST))
{
    if(!empty($_POST["show_pages"]))
    {
        $PHORUM["mod_forumstats"]["show_pages"]=(isset($_POST["show_pages"])) ? $_POST["show_pages"] : array();
        $PHORUM["mod_forumstats"]["show_local"]=($_POST["show_local"]) ? 1 : 0;
        $PHORUM["mod_forumstats"]["show_global"]=($_POST["show_global"]) ? 1 : 0;
        $PHORUM["mod_forumstats"]["cache_time"]=($_POST["cache_time"]) ? $_POST["cache_time"] : 60;
        $PHORUM["mod_forumstats"]["get_recent_user"]=($_POST["get_recent_user"]) ? 1 : 0;
    } else {
        $error = "Pages to show must be defined.";
    }

    if(empty($error))
    {
        phorum_db_update_settings(array(
            "mod_forumstats" => $PHORUM["mod_forumstats"]
        ));
        phorum_admin_okmsg("The settings were updated successfully");
    }
    else
    {
        phorum_admin_error($error);
    }
}

include_once "./include/admin/PhorumInputForm.php";
$frm = new PhorumInputForm ("", "post", "Save");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "forumstats");

$frm->addbreak("Edit Settings of the Forum Stats module");

$frm->addbreak();
$frm->addmessage("Caching the stats means that the module will only update the stats after the amount of time shown here.");
$frm->addrow("Cache time (minutes)", $frm->text_box("cache_time", $PHORUM["mod_forumstats"]["cache_time"]));

$frm->addbreak();

$frm->addmessage("Select which stats you want to show. Global means to show the summarized statistics of all forums together. Local means the stats of the current forum only.");
$frm->addrow("Which stats to show?", $frm->checkbox("show_local", "1", "Local",$PHORUM["mod_forumstats"]["show_local"]).$frm->checkbox("show_global", "1", "Global",$PHORUM["mod_forumstats"]["show_global"]));

$frm->addrow("Show most recent user?", $frm->checkbox("get_recent_user", "1", "Yes",$PHORUM["mod_forumstats"]["get_recent_user"]));


$frm->addbreak();
$frm->addmessage("Select the pages you would like to display the stats on, it will appear at the bottom of each selected page.");

$pages=array("index","read","list","post","search","control");
foreach($pages as $page)
{
  //$list[$forum_id]=$forum["name"];
  $checked = (@in_array($page, $PHORUM["mod_forumstats"]["show_pages"]))? 1 : 0;
  $frm->addrow($page, $frm->checkbox("show_pages[]", $page, "", $checked));
}

$frm->show();
?>
