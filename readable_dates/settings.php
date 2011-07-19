<?php

if (!defined("PHORUM_ADMIN")) return;

require_once("./mods/readable_dates/defaults.php");

// save settings
if(count($_POST))
{
    $PHORUM["mod_readable_dates"]["index"] =
        empty($_POST["index"]) ? 0 : 1;
    $PHORUM["mod_readable_dates"]["list"] =
        empty($_POST["list"]) ? 0 : 1;
    $PHORUM["mod_readable_dates"]["read"] =
        empty($_POST["read"]) ? 0 : 1;
    $PHORUM["mod_readable_dates"]["read_userregdate"] =
        empty($_POST["read_userregdate"]) ? 0 : 1;
    $PHORUM["mod_readable_dates"]["profile"] =
        empty($_POST["profile"]) ? 0 : 1;
    $PHORUM["mod_readable_dates"]["pm_list"] =
        empty($_POST["pm_list"]) ? 0 : 1;
    $PHORUM["mod_readable_dates"]["pm_read"] =
        empty($_POST["pm_read"]) ? 0 : 1;
    $PHORUM["mod_readable_dates"]["buddies"] =
        empty($_POST["buddies"]) ? 0 : 1;
    $PHORUM["mod_readable_dates"]["mod_announcements"] =
        empty($_POST["mod_announcements"]) ? 0 : 1;
    $PHORUM["mod_readable_dates"]["readable_numbers"] =
        empty($_POST["readable_numbers"]) ? 0 : 1;

    $PHORUM["mod_readable_dates"]["granularity"] =
        (int)$_POST["granularity"];

    $PHORUM["mod_readable_dates"]["max_time"] =
        empty($_POST['max_time']) ? '' : (int)$_POST["max_time"];

    phorum_db_update_settings(array(
        "mod_readable_dates" => $PHORUM["mod_readable_dates"]
    ));
    phorum_admin_okmsg("Settings updated");
}

include_once "./include/admin/PhorumInputForm.php";
$frm = new PhorumInputForm ("", "post", "Save settings");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "readable_dates");

$frm->addbreak("Edit settings for the Readable Dates module");

$row = $frm->addrow(
    "What granularity would you like to use?",
    $frm->select_tag(
        'granularity',
        array(
            0        => 'seconds',
            60       => 'minutes',
            3600     => 'hours',
            86400    => 'days',
            604800   => 'weeks',
            2592000  => 'months',
            30758400 => 'years'
        ),
        $PHORUM['mod_readable_dates']['granularity']
    )
);
$frm->addhelp($row, "What granularity would you like to use?",
    "By changing the granularity, you can set what interval you want
     to use as the smallest one for formatting the dates. E.g. if you
     choose for seconds here, then you might get \"50 seconds ago\"
     as the date. If you set granularity to minutes, then the same date
     would show up as \"this minute\".<br/><br/>
     The interval that you select here will not be used for all formatted
     dates. So you do not have to worry about getting things like
     \"3024000 seconds ago\" as a date if you select seconds for the
     interval. For this example, it would be \"five weeks ago\" instead."
);

$row = $frm->addrow(
    "What is the time limit that you want to use for formatting?",
    $frm->select_tag(
        'max_time',
        array(
            0          => 'no limit, format all dates',
            3600       => ' 1 hour',
            7200       => ' 2 hours',
            10800      => ' 3 hours',
            14400      => ' 4 hours',
            28800      => ' 8 hours',
            43200      => '12 hours',
            86400      => ' 1 day',
            172800     => ' 2 days',
            259200     => ' 3 days',
            345600     => ' 4 days',
            432000     => ' 5 days',
            518400     => ' 6 days',
            604800     => ' 1 week',
            1209600    => ' 2 weeks',
            1814400    => ' 3 weeks',
            2419200    => ' 4 weeks',
            2628000    => ' 1 month',
            5256000    => ' 2 months',
            7884000    => ' 3 months',
            10512000   => ' 4 months',
            13140000   => ' 5 months',
            15724800   => ' 6 months',
            18396000   => ' 7 months',
            21024000   => ' 8 months',
            23652000   => ' 9 months',
            26280000   => '10 months',
            28908000   => '11 months',
            31536000   => ' 1 year',
        ),
        $PHORUM['mod_readable_dates']['max_time']
    )
);
$frm->addhelp($row, "Time limit to use for formatting",
    "If you set a time limit, then dates that are older than
     this time limit will not be formatted by this module.
     For those, the default formatting that is applied by Phorum
     will be used."
);

$row = $frm->addrow(
    "Enable readable numbers? (one, two, three instead of 1, 2, 3)",
    $frm->checkbox(
        "readable_numbers", "1", "Yes",
        $PHORUM['mod_readable_dates']['readable_numbers']
    )
);
$frm->addhelp($row, "Enable readable numbers?",
    "If you enable this feature, then numbers will be translated to their
     textual representation. So instead of \"3 days ago\", \"three days
     ago\" will be shown.<br/><br/>
     The translation strings for the numbers can be found in the
     language files for this module (mods/readable_dates/lang/*)."
);

$frm->addbreak("Select the pages for which you want to format the dates");

$frm->addrow("Format dates on the index page (list of forums)", $frm->checkbox("index", "1", "Yes", $PHORUM["mod_readable_dates"]["index"]));
$frm->addrow("Format dates on the list page (list of messages)", $frm->checkbox("list", "1", "Yes", $PHORUM["mod_readable_dates"]["list"]));
$frm->addrow("Format dates on the read page", $frm->checkbox("read", "1", "Yes", $PHORUM["mod_readable_dates"]["read"]));
$frm->addrow("Format registration date (Registered: ... ago) for users on the read page", $frm->checkbox("read_userregdate", "1", "Yes", $PHORUM["mod_readable_dates"]["read_userregdate"]));
$frm->addrow("Format dates on the user profile page", $frm->checkbox("profile", "1", "Yes", $PHORUM["mod_readable_dates"]["profile"]));

// Check if the Phorum version is high enough for the upcoming three
// options to work. In Phorum 5.2.7, some hooks were added to support these.
require_once('./include/version_functions.php');
$warn = '';
if (phorum_compare_version(PHORUM, '5.2.7') == -1) {
    $warn = '<div style="color:red">This option will only work for Phorum 5.2.7 or higher</div>';
}
$frm->addrow("Format dates on the private messages list page (list of messages)$warn", $frm->checkbox("pm_list", "1", "Yes", $PHORUM["mod_readable_dates"]["pm_list"]));
$frm->addrow("Format dates on the private messages read page$warn", $frm->checkbox("pm_read", "1", "Yes", $PHORUM["mod_readable_dates"]["pm_read"]));
$frm->addrow("Format dates on the buddy list page$warn", $frm->checkbox("buddies", "1", "Yes", $PHORUM["mod_readable_dates"]["buddies"]));

// This one is special. The announcement module should handle this
// by calling the readable dates formatting code, but since this is
// a core module, we'll make an exception and handle the data
// formatting directly from this module.
$frm->addrow("Format dates for the announcements module", $frm->checkbox("mod_announcements", "1", "Yes", $PHORUM["mod_readable_dates"]["mod_announcements"]));

$frm->show();

?>
