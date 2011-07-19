<?php
if (!defined("PHORUM_ADMIN")) return;

// Find the styles that are available.
$base = "./mods/topic_poll/styles";
$styles = array();
$dir = opendir($base);
if (! $dir) die("Cannot opendir $base");
while ($entry = readdir($dir)) {
   if ($entry != '.' && $entry != '..' &&
       file_exists("$base/$entry/style.css")) {
      $preview = 0;
      if (file_exists("$base/$entry/preview.gif")) {
        $preview = $GLOBALS["PHORUM"]["http_path"] .
                   "/$base/$entry/preview.gif";
      }
      $styles[$entry] = $preview;
    }
}
closedir ($dir);

// Build the code for switching styles.
?>
<link rel="stylesheet" type="text/css"
      href="./mods/topic_poll/topic_poll.css" /> <?php

foreach ($styles as $id => $preview) {
    print "<link rel=\"alternate stylesheet\" type=\"text/css\" " .
          "title=\"" . htmlspecialchars($id) . "\" " .
          "href=\"" . $PHORUM["http_path"] . "/mods/topic_poll/styles/" .
          htmlspecialchars($id) . "/style.css\" " .
          "/>\n";
}
?>

<script type="text/javascript">
  function update_preview(style_id)
  {
    if (! document.getElementById) return;
    sel = document.getElementById("style_select");
    img = document.getElementById("preview_image");
    div = document.getElementById("preview_div");

    style_id = sel.options[sel.selectedIndex].value;

    var i, a, main;
    for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
      if(a.getAttribute("rel").indexOf("style") != -1
         && a.getAttribute("title")) {
        a.disabled = true;
        if(a.getAttribute("title") == style_id) a.disabled = false;
      }
    }
  }
</script> <?php

// Retrieve settings for the current forum and apply default settings.
$all_settings = isset($PHORUM["mod_topic_poll"])
              ? $PHORUM["mod_topic_poll"] : array();
$settings = isset($all_settings[$forum_id])
          ? $all_settings[$forum_id] : array();
require_once('settings.default.php');
foreach ($PHORUM["mod_topic_poll_defaults"] as $key => $val) {
    if (!isset($settings[$key])) {
        $settings[$key] = $val;
    }
}

// save settings
if(count($_POST) && isset($_POST["permission"]))
{

    $settings["permission"]   = (int) $_POST["permission"];
    $settings["ip_blocktime"] = (int) $_POST["ip_blocktime"];
    $settings["allow_revoke"] = isset($_POST["allow_revoke"]) ? 1 : 0;
    $settings["allow_novoteview"] = isset($_POST["allow_novoteview"])
                                  ? 1 : 0;

    $settings["subjecttag"] = (int) $_POST['subjecttag'];
    $settings["subjecttag_marknew"] = isset($_POST["subjecttag_marknew"])
                                    ? 1 : 0;
    $settings["style"] = basename($_POST["style"]);

    $all_settings[$forum_id] = $settings;

    phorum_db_update_settings(array("mod_topic_poll" => $all_settings));
    phorum_admin_okmsg("The settings were updated successfully.");

    $PHORUM["mod_topic_poll"] = $all_settings;

    // Back to the forum selection screen.
    include("settings.selectforum.php");
    exit(0);
}

require_once('./include/admin/PhorumInputForm.php');
$frm = new PhorumInputForm ("", "post", "Save settings");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "topic_poll");
$frm->hidden("forum_id", $forum_id);

$frm->addbreak("Settings for forum");

// ----------------------------------------------------------------------
// Permission
// ----------------------------------------------------------------------

// Authorization options for creating polls.
$choices = array(
  "0" => "Nobody",
  "1" => "Any user (including anonymous)",
  "2" => "Registered users",
  "3" => "Moderators for this forum",
  "4" => "Phorum administrators",
);

$frm->addrow(
    "Who is allowed to create polls in this forum?",
    $frm->select_tag("permission", $choices, $settings["permission"])
);

// ----------------------------------------------------------------------
// IP address blocking
// ----------------------------------------------------------------------

$row = $frm->addrow(
    "IP-address blocking time for anonymous votes",
    $frm->text_box("ip_blocktime", $settings["ip_blocktime"], 10) .
    " minutes"
);
$frm->addhelp($row,
    "IP-address blocking",
    "To prevent multiple votes from being cast by anonymous users (in case
     anonmous voting is enabled for the poll), a cookie is set and the
     IP-address that the vote was cast from is blocked for voting.
     Here you can configure how long this IP-address block should be
     active. If you do not understand what this option is for, just
     accept the default."
);

// ----------------------------------------------------------------------
// Revoking votes
// ----------------------------------------------------------------------

$row = $frm->addrow(
    "Allow revoking a vote for registered users?",
    $frm->checkbox("allow_revoke", "1", "Yes", $settings["allow_revoke"])
);
$frm->addhelp(
    $row, "Allow revoking",
    "You can allow registered users to revoke a vote they have cast,
     so they can vote for another poll answer."
);

// ----------------------------------------------------------------------
// Viewing results without voting
// ----------------------------------------------------------------------

$row = $frm->addrow(
    "Allow viewing of poll results without voting?",
    $frm->checkbox(
        "allow_novoteview", "1", "Yes",
        $settings["allow_novoteview"]
    )
);

// ----------------------------------------------------------------------
// Message list tagging
// ----------------------------------------------------------------------

$choices = array(
  "0" => "No",
  "1" => "Automatically add an icon to the subjects",
  "2" => "Setup template variables to use in the templates"
);

$row = $frm->addrow(
    "Add info to the message list for messages with polls?",
    $frm->select_tag("subjecttag", $choices, $settings["subjecttag"])
);
$frm->addhelp(
    $row, "Add info to the message list for messages with polls?",
    "If you want to show in the message list whether a thread contains a
     poll or not, you can set that up through this option.<br/><br/>
     If you choose to add an icon automatically, then the icon will be
     appended to the subject of the threads.<br/><br/>You can also
     choose to only setup template variables, which you can use to
     modify the list.tpl and list_threads.tpl. If you do so, the
     template variable {MESSAGES->topic_poll} will be set inside the
     {LOOP MESSAGES} loop.  It can contain three values: 0 (no poll
     available), 1 (poll available), 2 (new poll available, only
     available if the option \"<i>Use a special tag / template variable
     for new polls?</i>\" is enabled as well.)"
);

$row = $frm->addrow(
    "Use a special icon / template variable for new polls?",
    $frm->checkbox(
        "subjecttag_marknew", "1", "Yes",
        $settings["subjecttag_marknew"]
    )
);

$frm->addhelp(
    $row, "Special tag for new polls",
    "If you enable this option, then a special tag will be used for tagging
     messages which contain polls for which the visitor can still vote.
     Beware that this takes some extra processing. On performance
     sensitive systems, you might want to disable this option."
);

// ----------------------------------------------------------------------
// Style selection
// ----------------------------------------------------------------------

$styleoptions = array();
foreach ($styles as $id => $preview) {
    $styleoptions[$id] = $id;
}

$row = $frm->addrow(
    "Select the style to use for the polls",
    $frm->select_tag(
        "style", $styleoptions, $settings["style"],
        'id="style_select" onchange="update_preview()"'
    )
);

$frm->addhelp(
    $row, "Select style",
    "To customize the way polls look, you can choose a style here.
     Each style is basically a customized CSS which takes care of
     styling the poll. If you want to create your own style, then create
     an extra subdirectory below the \"styles\" directory of this mod,
     with at least a style.css file in it."
);

// Create a fake poll to be able to show the style preview.
$poll = array(
    'question' => 'Do you like this preview?',
    'answers' => array(
        0 => 'Yes, I do!',
        1 => 'No, I hate it!',
        2 => 'I have no opinion on this.'
    ),
    'votes' => array(
        0 => 5,
        1 => 2,
        2 => 7
    ),
    'total_votes' => 14,
    'votingtime' => 0,
    'permission' => "user",
    'active' => 1,
    'cache_id' => 1,
);
$messages = array(1 => array(
    "body" => "",
    "meta" => array("mod_topic_poll" => $poll),
    "message_id" => 1,
    "parent_id" => 0,
    "datestamp" => time(),
    "raw_datestamp" => time()
));
require_once('./mods/topic_poll/lang/english.php');
require_once('./mods/topic_poll/topic_poll.php');
$messages = phorum_mod_topic_poll_format($messages, true);
$messages = phorum_mod_topic_poll_format_fixup($messages);

$frm->addmessage($messages[1]["body"]);

$frm->show();

print "<script type=\"text/javascript\">update_preview()</script>";

?>
