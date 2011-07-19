<?php
if (!defined("PHORUM_ADMIN")) return;

// Load default settings for this module.
require_once('./mods/onlineusers/defaults.php');

// save settings
if(count($_POST))
{
    $PHORUM["mod_onlineusers"]["idle_time"]=((int)$_POST["idle_time"] > 0) ? (int)$_POST["idle_time"] : 1;
    $PHORUM["mod_onlineusers"]["cache_time"]=($_POST["cache_time"] >= 0) ? (int) $_POST["cache_time"] : 0;
    $PHORUM["mod_onlineusers"]["show_pages"]=isset($_POST["show_pages"]) ? $_POST["show_pages"] : array();
    $PHORUM["mod_onlineusers"]["disable_guests"]=isset($_POST["disable_guests"]) ? 1 : 0;
    $PHORUM["mod_onlineusers"]["show_idle_time"]=isset($_POST["show_idle_time"]) ? 1 : 0;
    $PHORUM["mod_onlineusers"]["keep_records"]=isset($_POST["keep_records"]) ? 1 : 0;
    $PHORUM["mod_onlineusers"]["display_forum_readers"]=isset($_POST["display_forum_readers"]) ? 1 : 0;
    $PHORUM["mod_onlineusers"]["indicate_admin_users"]=isset($_POST["indicate_admin_users"]) ? 1 : 0;
    $PHORUM["mod_onlineusers"]["view_permission"]=(int) $_POST["view_permission"];

    phorum_db_update_settings(array(
        "mod_onlineusers" => $PHORUM["mod_onlineusers"]
    ));

    phorum_admin_okmsg("The module setting were updated successfully");
}

if (empty($PHORUM['track_user_activity']) || $PHORUM['track_user_activity'] > (int)$PHORUM["mod_onlineusers"]["idle_time"]*60) {
    $cur = empty($PHORUM['track_user_activity'])
         ? 'disabled'
         : (round($PHORUM['track_user_activity']/6) / 10) . ' minute(s)';
    phorum_admin_error(
        "<strong>Notice:</strong>
         This module uses the Phorum user tracking feature to determine
         which registered users are online. Therefore user tracking must be
         enabled and set to a value below the configured idle time.
         Currently, the user tracking is set to \"$cur\", which will
         cause problems for this module. Please, go to
         <a href=\"{$PHORUM['admin_http_path']}?module=settings\">the
         General Settings page</a> and set the \"Track User Usage\"
         option to a value at or below
         {$PHORUM["mod_onlineusers"]["idle_time"]} minute(s)."
    );
}

include_once "./include/admin/PhorumInputForm.php";
$frm = new PhorumInputForm ("", "post", "Save");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "onlineusers");

$frm->addbreak("Edit Settings for the Online Users module");

$row = $frm->addrow("Idle time before a user is offline (minutes)", $frm->text_box("idle_time", $PHORUM["mod_onlineusers"]["idle_time"]));
$frm->addhelp($row, "Idle time before a user is offline (minutes)", "Idle time is the maximum amount of time between user actions to still consider the user online. If set to 5 minutes, a user inactive for less than 5 minutes will be shown as online. This shouldn't be too low, as a user can be reading a thread for several minutes and still be online.<br/><br/>The \"Track User Usage\" feature on the General Settings page is used for tracking registered users. The configuration value for that feature should be at or below the idle time that is configured here to make the module work correctly (if there is some conflict within this constraint, you will get a warning notice in this settings screen).");

$row = $frm->addrow("Cache time (seconds)", $frm->text_box("cache_time", $PHORUM["mod_onlineusers"]["cache_time"]));
$frm->addhelp($row, "Cache time (seconds)", "With caching enabled, the module will only update the list after the amount of time shown here. If your database server is very busy, caching will help increasing performance. Set this value to 0 to disable caching. Recommended value: between 60 (1 minute) and 1200 (20 minutes).");

$row = $frm->addrow("Disable Guest Counting?", $frm->checkbox("disable_guests", "1", "Disable guest counting", $PHORUM["mod_onlineusers"]["disable_guests"]));
$frm->addhelp($row, "Disable Guest Counting:", "Anonymous users (Guests) are tracked using an IP address + Browser name based method. This will under-estimate multiple guests from behind proxies/NAT, but will not over-estimate guests without cookies enabled (as a cookie method would do). If you do not wish to keep statistics on guests, you can disable guest tracking completely.");

$row = $frm->addrow("Keep records of maximum number of users / guests?", $frm->checkbox("keep_records", "1", "Keep records", $PHORUM["mod_onlineusers"]["keep_records"]));
$frm->addhelp($row, "Keep Records?", "Records can be tracked for the maximum amount of users and guests that have ever been online at the same time.");

$row = $frm->addrow("Indicate administrators in the online user list?", $frm->checkbox("indicate_admin_users", "1", "Indicate administrators", $PHORUM["mod_onlineusers"]["indicate_admin_users"]));
$frm->addhelp($row, "Indicate administrators in the online user list?", "If this option is enabled, administrator users will be indicated in the online user list by adding \"(admin)\" behind their name.");

$row = $frm->addrow("Display number of forum readers?", $frm->checkbox("display_forum_readers", "1", "Display forum readers", $PHORUM["mod_onlineusers"]["display_forum_readers"]));
$frm->addhelp($row, "Display number of forum readers?", "By enabling this feature, you can show the number of visitors that are inside a forum for each forum on the index page.");

$row = $frm->addrow("Show idle time for online users?", $frm->checkbox("show_idle_time", "1", "Show idle time", $PHORUM["mod_onlineusers"]["show_idle_time"]));
$frm->addhelp($row, "Show idle time for online users?", "The time a user has been idle can be displayed after their name in the online user list. Note that enabling this feature might be considered invasive by some users.<br/><br/>For best results with this feature, \"Track User Usage\" on the General Settings page should be set to a really low value (preferably \"Constantly\"), otherwise users might show up as idle for too long.");

$frm->addrow(
    "Show the online users to what users?",
    $frm->select_tag('view_permission', array(
        ONLINEUSERS_PERM_ALL => 'All visitors',
        ONLINEUSERS_PERM_REGISTEREDUSERS => 'Registered users',
        ONLINEUSERS_PERM_ADMINS => 'Only administrators'
    ), $PHORUM['mod_onlineusers']['view_permission'])
);

$row = $frm->addbreak("Select pages on which you want to display online user information");
$frm->addhelp($row, "Pages", "The online user information will be shown automatically near the footer of the selected pages.");

$pages=array(
  "index"   => "The overview of available forums (index)",
  "list"    => "The overview of threads in a forum (list)",
  "read"    => "The read pages for a thread (read)",
  "post"    => "Post a message (post)",
  "search"  => "Search page + results (search)",
  "control" => "User control center (control)"
);
$options = array(
  0 => 'Do not show',
  1 => 'Show in the footer',
  2 => 'Show in the header'
);
foreach($pages as $page => $desc){
    $selected = empty($PHORUM['mod_onlineusers']['show_pages'][$page])
             ? 0 : $PHORUM['mod_onlineusers']['show_pages'][$page];
    $frm->addrow($desc, $frm->select_tag("show_pages[$page]", $options, $selected));
}

$frm->show();
?>
