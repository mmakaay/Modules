<?php

if(!defined("PHORUM")) return;

require_once("./mods/readable_dates/defaults.php");
require_once("./mods/readable_dates/date_format.php");

function phorum_mod_readable_dates_index($data)
{
    global $PHORUM;

    // Only format index dates if this is enabled in the configuration.
    if (!$PHORUM['mod_readable_dates']['index']) return $data;

    foreach ($data as $id => $message)
    {
        // Skip forum folders.
        if ($message['folder_flag']) continue;

        // Format the forum's last post time.
        $message['orig_last_post'] = $message['last_post'];
        if (isset($message['raw_last_post'])) {
            $data[$id]['last_post'] = mod_readable_dates_format_date(
                $message['raw_last_post'], $message['last_post']
            );
        }
    }

    return $data;
}


function phorum_mod_readable_dates_list($data)
{
    global $PHORUM;

    // Only format list dates if this is enabled in the configuration.
    if (!$PHORUM["mod_readable_dates"]["list"]) return $data;

    foreach ($data as $id => $message)
    {
        // Format the datestamp.
        $message["orig_datestamp"] = $message["datestamp"];
        $data[$id]["datestamp"] = mod_readable_dates_format_date(
            $message["raw_datestamp"], $message['datestamp']
        );

        // Format the last post time. This does not apply to a threaded
        // list view, so we skip this step when needed.
        if (isset($message['raw_lastpost']))
        {
            $message["orig_lastpost"] = $message["lastpost"];
            $data[$id]["lastpost"]  = mod_readable_dates_format_date(
                $message["raw_lastpost"], $message['lastpost']
            );
        }
    }

    return $data;
}


function phorum_mod_readable_dates_read($data)
{
    $PHORUM = $GLOBALS["PHORUM"];

    // Only format read dates if this is enabled in the configuration.
    if (!$PHORUM["mod_readable_dates"]["read"] &&
        !$PHORUM["mod_readable_dates"]["read_userregdate"]) return $data;

    foreach ($data as $id => $message)
    {
        // Format the user registration date.
        // Phorum itself already does some readable date like stuff on its own,
        // so we have to format orig_date_added ourselves here.
        if ($PHORUM["mod_readable_dates"]["read_userregdate"] &&
            !empty($data[$id]['user']['date_added'])) {
            $data[$id]['user']['orig_date_added'] =
                phorum_date(
                    $PHORUM['short_date'],
                    $message['user']['raw_date_added']
                );
            $data[$id]['user']['date_added'] = mod_readable_dates_format_date(
                $message['user']['raw_date_added'],
                phorum_date(
                    $PHORUM['short_date'],
                    $message['user']['raw_date_added']
                )
            );
        }

        // Configuration not enabled for handling further dates on the
        // read pages? Then continue with the next message.
        if (!$PHORUM["mod_readable_dates"]["read"]) continue;

        // Format the datestamps.
        if (isset($message['raw_datestamp'])) {
            $data[$id]['orig_datestamp'] = $message['datestamp'];
            $data[$id]['datestamp'] = mod_readable_dates_format_date(
                $message['raw_datestamp'], $message['datestamp']
            );
            if (isset($message['short_datestamp'])) {
                $data[$id]['orig_short_datestamp'] =
                    $message['short_datestamp'];
                $data[$id]['short_datestamp'] = mod_readable_dates_format_date(
                    $message['raw_datestamp'], $message['short_datestamp']
                );
            }
        }

        // If we have no message body here, we're done.
        if (! isset($message['body'])) continue;

        // Rebuild the message that has been used for the edit count.
        // Since this is a part of the message body itself, we have to
        // take this route to be able to format the date in it :-(
        $max = $PHORUM['mod_readable_dates']['max_time'];
        if (!empty($message['meta']['edit_count']) &&
            (empty($max) || (time() - $message['meta']['edit_date']) <= $max))
        {
            // Build the string to replace.
            $src = str_replace(
                "%count%",
                $message['meta']['edit_count'],
                $PHORUM["DATA"]["LANG"]["EditedMessage"]
            );
            $src = str_replace(
                "%lastedit%",
                phorum_date(
                    $PHORUM["short_date_time"],
                    $message['meta']['edit_date']
                ),
                $src
            );
            $src = str_replace (
                "%lastuser%",
                $message['meta']['edit_username'],
                $src
            );

            // Build the replacement string.
            $lang = $PHORUM['DATA']['LANG']['mod_readable_dates'];
            $dst = isset($lang['edit_message'])
                 ? $lang['edit_message']
                 : $PHORUM['DATA']['LANG']['EditedMessage'];
            $dst = str_replace(
                "%count%",
                $message["meta"]["edit_count"],
                $dst
            );
            $dst = str_replace(
                "%lastuser%",
                $message["meta"]["edit_username"],
                $dst
            );
            $dst = str_replace(
                "%lastedit%",
                mod_readable_dates_format_date($message['meta']['edit_date']),
                $dst
            );

            // Replace the edit message and store the result.
            $data[$id]["body"] = str_replace($src, $dst, $message["body"]);
        }
    }

    return $data;
}

function phorum_mod_readable_dates_profile($data)
{
    global $PHORUM;

    // Only format profile dates if this is enabled in the configuration.
    if (!$PHORUM['mod_readable_dates']['profile']) return $data;

    // Format the registered date.
    $data['orig_date_added'] = $data['date_added'];
    $data['date_added'] = mod_readable_dates_format_date(
        $data['raw_date_added'], $data['date_added']
    );

    // These fields might not be set if the user activity is hidden.
    $data['orig_date_last_active'] = $data['date_last_active'];
    if (isset($data['date_last_active'])) {
        // Format the last activity date.
        $data['date_last_active'] = mod_readable_dates_format_date(
            $data['raw_date_last_active'], $data['date_last_active']
        );
    }

    return $data;
}

function phorum_mod_readable_dates_pm_read($data)
{
    global $PHORUM;

    // Only format pm read dates if this is enabled in the configuration.
    if (!$PHORUM['mod_readable_dates']['pm_read']) return $data;

    // Format the message date.
    $data['orig_date'] = $data['data'];
    $data['date'] = mod_readable_dates_format_date(
        $data['raw_date'], $data['date']
    );

    return $data;
}

function phorum_mod_readable_dates_pm_list($data)
{
    global $PHORUM;

    // Only format list dates if this is enabled in the configuration.
    if (!$PHORUM['mod_readable_dates']['pm_list']) return $data;

    // Format the message dates.
    foreach ($data as $id => $message) {
        $data[$id]['orig_date'] = $data[$id]['date'];
        $data[$id]['date'] = mod_readable_dates_format_date(
            $message['raw_date'], $message['date']
        );
    }

    return $data;
}

function phorum_mod_readable_dates_buddy_list($data)
{
    global $PHORUM;

    // Only format buddy list dates if this is enabled in the configuration.
    if (!$PHORUM['mod_readable_dates']['buddies']) return $data;

    // Format the last active dates.
    foreach ($data as $id => $buddy) {
        $data[$id]['orig_date_last_active'] = $data[$id]['date_last_active'];
        $data[$id]['date_last_active'] = mod_readable_dates_format_date(
            $buddy['raw_date_last_active'], $buddy['date_last_active']
        );
    }

    return $data;
}


function phorum_mod_readable_dates_format($data)
{
    global $PHORUM;

    // A bit of trickery to detect from what module this hook was called.
    $bt = debug_backtrace();

    // The announcement module's formatting code.
    if (isset($bt[3]) &&
        $bt[3]['function'] == 'phorum_setup_announcements')
    {
        // Only format announcements dates if this is enabled in
        // the configuration.
        if (!$PHORUM['mod_readable_dates']['mod_announcements']) {
            return $data;
        }

        foreach ($data as $id => $message) {
            $data[$id]['orig_modifystamp'] = $message['modifystamp'];
            $data[$id]['lastpost'] = mod_readable_dates_format_date(
                $message['modifystamp']
            );
            $data[$id]['orig_datestamp'] = $message['datestamp'];
            $data[$id]['datestamp'] = mod_readable_dates_format_date(
                $message['raw_datestamp']
            );
        }
    }

    return $data;
}

function phorum_mod_readable_dates_format_date($time)
{
    return mod_readable_dates_format_date($time);
}

?>
