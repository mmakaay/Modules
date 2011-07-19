<?php

if (!defined("PHORUM_ADMIN")) return;

if (count($_POST))
{
    require_once('./mods/last_forum_post/last_forum_post.php');
    $forums = phorum_db_get_forums();

    print "<h2>Rebuild last post info</h2>";
    print "<ul>";
    foreach ($forums as $forum) {
        if ($forum['folder_flag']) continue;
        if (!is_array($forum['forum_path'])) {
            $forum['forum_path'] = unserialize($forum['forum_path']);
        }
        $path = implode(" / ", $forum['forum_path']);
        print "<li> $path</li>";
        mod_last_forum_post_update($forum['forum_id']);
    }
    print "</ul>";
    print "All forums were successfully updated.<br/><br/>";
}

require_once('./include/admin/PhorumInputForm.php');
$frm = new PhorumInputForm ("", "post", "Rebuild last post info for all forums");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "last_forum_post");

$frm->addbreak("General settings for the Last Forum Post module");

$frm->addmessage(
    "This module does not have any configuration settings. You can use
     this settings page to rebuild the last post information for all
     available forums. This is mainly useful after installing the
     module, since last post information for a forum will only become
     available after a user posts a message in that forum."
);

$frm->show();
?>
