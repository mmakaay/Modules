<?php

if(!defined("PHORUM")) return;

function phorum_mod_filelist_read($data)
{
    // The filelist is only shown on the read pages.
    if (phorum_page != 'read') return $data;

    $GLOBALS["PHORUM"]["DATA"]["MOD_FILELIST"] = NULL;

    // Find the thread id.
    reset($data);
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
    foreach ($allmsgs as $msg)
    {
        if (isset($msg["meta"]["attachments"]) &&
            is_array($msg["meta"]["attachments"]))
        {
            require_once('./include/api/file_storage.php');

            foreach ($msg["meta"]["attachments"] as $attachfile)
            {
                // We need to retrieve the data for the file from the
                // file storage system. Using the read access check,
                // we will get all info that we need. No need to do any
                // real retrieval of file content here.
                $file_id = $attachfile["file_id"];
                $file = phorum_api_file_check_read_access($file_id);
                if (empty($file)) continue;

                $attachments[] = array(
                    "MESSAGE_ID"    => $msg["message_id"],
                    "AUTHOR"        => !empty($GLOBALS['PHORUM']['custom_display_name'])
                                     ? $msg["author"]
                                     : htmlspecialchars($msg["author"]),
                    "USER_ID"       => $msg["user_id"],
                    "RAW_DATESTAMP" => $file["add_datetime"],
                    "DATESTAMP"     => phorum_date($GLOBALS["PHORUM"]["short_date"], $file["add_datetime"]),
                    "FILE_ID"       => $file["file_id"],
                    "NAME"          => htmlspecialchars($file["filename"]),
                    "RAW_SIZE"      => $file["filesize"],
                    "SIZE"          => phorum_filesize($file["filesize"]),
                    "URL"           => array(
                      "FILE"        => phorum_get_url(PHORUM_FILE_URL, "file={$file["file_id"]}", "filename=".urlencode($file["filename"])),
                      "DOWNLOAD"    => phorum_get_url(PHORUM_FILE_URL, "file={$file["file_id"]}", "filename=".urlencode($file["filename"]), "download=1"),
                      "PROFILE"     => ($msg["user_id"] ? phorum_get_url(PHORUM_PROFILE_URL, $msg["user_id"]) : false),
                      "READ"        => phorum_get_url(PHORUM_READ_URL, $thread, $msg["message_id"]),
                    ),
                );
            }
        }
    }

    if (count($attachments)) {
        $GLOBALS["PHORUM"]["DATA"]["MOD_FILELIST"] = $attachments;
    }

    return $data;
}

function phorum_mod_filelist_tpl_filelist($args)
{
    // No filelist available? Then return right away.
    if (empty($args[0])) return $args;

    // Setup the data that is needed in the template.
    $PHORUM["DATA"] = array(
      "FILES" => $args[0],
      "LANG"  => $args[1]
    );

    // Display the file list.
    include phorum_get_template("filelist::filelist");

    return $args;
}

function phorum_mod_filelist_after_header()
{
    global $PHORUM;
    if (phorum_page == 'read' &&
        !empty($PHORUM['DATA']['MOD_FILELIST']) &&
        !empty($PHORUM['mod_filelist']['show_after_header'])) {
        $PHORUM['DATA']['FILES'] = $PHORUM['DATA']['MOD_FILELIST'];
        include phorum_get_template("filelist::filelist");
    }
}

function phorum_mod_filelist_before_footer()
{
    global $PHORUM;
    if (phorum_page == 'read' &&
        !empty($PHORUM['DATA']['MOD_FILELIST']) &&
        !empty($PHORUM['mod_filelist']['show_before_footer'])) {
        $PHORUM['DATA']['FILES'] = $PHORUM['DATA']['MOD_FILELIST'];
        include phorum_get_template("filelist::filelist");
    }
}

?>
