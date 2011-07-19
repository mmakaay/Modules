<?php
if (!defined('PHORUM')) return;

// The first message in a thread is used to hold all the main 
// file data. That one is not really considered to be part of 
// the discussion. Discussions starts on the second message in
// the Phorum thread. What we arrange for here, is to always
// collect the starting message data and put it in {THREAD}.
// That data is also filled with extra data that is needed
// for the repository template.
//
// We cannot remove the thread starting message from the messages list here,
// because that would confuse read.php. We'll have to handle
// that in the templates.
function phorum_mod_thread_start_on_each_read_page_read($read)
{
    // Retrieve the thread data.
    list ($firstid, $firstmsg) = each ($read);
    if (empty($firstmsg["parent_id"])) {
        $thread = $firstmsg;
    } else {
        $thread = phorum_db_get_message($firstmsg["parent_id"]);
    }

    // Run formatting on the thread data.
    list ($thread) = phorum_format_messages(array($thread));

    $GLOBALS["PHORUM"]["DATA"]["THREAD"] = $thread;

    return $read;
}
?>
