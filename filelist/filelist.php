<?php

if(!defined("PHORUM")) return;

function phorum_mod_filelist_read($data)
{
    $GLOBALS["PHORUM"]["MOD_FILELIST"] = NULL;

    // Find the thread id.
    list ($firstid, $firstmsg) = each($data);
    if (!isset($firstmsg["thread"])) return $data; # paranoia
    $thread = $firstmsg["thread"];
    $forum = $firstmsg["forum_id"];

    // We do not show the filelist by default. So if we don't have
    // module settings at all, we can return immediately.
    if (! isset($GLOBALS["PHORUM"]["mod_filelist"]) ||
        ! isset($GLOBALS["PHORUM"]["mod_filelist"][$forum])) {
        return $data;
    }

    // Check if and how we want to display the file list for the current forum.
    $setting = $GLOBALS["PHORUM"]["mod_filelist"][$forum];
    if ($setting == "ALL") { 
        // NOOP, continue showing the file list.
    } elseif ($setting == "FIRST") { 
        // Only continue if we're at the first message.
        if ($thread != $firstmsg["message_id"]) return $data;
    } else {
        return $data;
    }

    $attachments = array();
    $allmsgs = phorum_db_get_messages($thread);
    foreach ($allmsgs as $id => $msg) {
        if (isset($msg["meta"]["attachments"]) && is_array($msg["meta"]["attachments"])) {
            foreach ($msg["meta"]["attachments"] as $id => $attachfile)
            {
                // We need to retrieve the file itself from the
                // database, so we can use the time at which the
                // file was uploaded.
                $file = phorum_db_file_get($attachfile["file_id"]); 
                if (! $file) continue;

                $attachments[] = array(
                    "message_id"    => $msg["message_id"],
                    "author"        => htmlspecialchars($msg["author"]),
                    "user_id"       => $msg["user_id"],
                    "datestamp"     => $file["add_datetime"],
                    "fmt_datestamp" => phorum_date($GLOBALS["PHORUM"]["short_date"], $file["add_datetime"]),
                    "file_id"       => $file["file_id"],
                    "name"          => htmlspecialchars($file["filename"]),
                    "size"          => $file["filesize"],
                    "fmt_size"      => phorum_filesize($file["filesize"]),
                    "link_file"     => phorum_get_url(PHORUM_FILE_URL, "file={$file["file_id"]}"),
                    "link_author"   => ($msg["user_id"] ? phorum_get_url(PHORUM_PROFILE_URL, $msg["user_id"]) : false),
                    "link_message"  => phorum_get_url(PHORUM_READ_URL, $thread, $msg["message_id"]),
                );
            }
        }
    }

    if (count($attachments)) {
        $GLOBALS["PHORUM"]["MOD_FILELIST"] = $attachments;
    }

    return $data;
}

function phorum_mod_filelist_tpl_filelist()
{
    if ($GLOBALS["PHORUM"]["MOD_FILELIST"] == NULL) {
        return;
    }

    $data = $GLOBALS["PHORUM"]["MOD_FILELIST"];
    $lang = $GLOBALS["PHORUM"]["DATA"]["LANG"];
    $tpl = $GLOBALS["PHORUM"]["template"];
    if (file_exists("./mods/filelist/templates/{$tpl}.php")) {
        include("./mods/filelist/templates/{$tpl}.php");
    } else {
        include("./mods/filelist/templates/default.php");
    }
}

function phorum_mod_filelist_real_name_add_rules($files)
{
    $files[] = "./mods/filelist/rewrite_rules.src";
    return $files;
}

?>
