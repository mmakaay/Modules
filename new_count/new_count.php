<?php
if (!defined("PHORUM")) return;

function phorum_mod_new_count_list($messages)
{
    global $PHORUM;

    // We do not have new message counts if the user is not logged in.
    if (!$PHORUM["DATA"]["LOGGEDIN"]) return $messages;

    // Compute the total number of unread messages and threads.
    list ($PHORUM["DATA"]["NEW_MESSAGES"], $PHORUM["DATA"]["NEW_THREADS"]) =
        phorum_db_newflag_get_unread_count($PHORUM["forum_id"]);

    // For threaded view, we do not need totals per thread.
    if (!isset($PHORUM["threaded_list"]) || !$PHORUM["threaded_list"])
    {
        // Compute the number of new messages in the threads.
        foreach ($messages as $key => $message)
        {
            // Make sure that the new_count variable is set, to prevent
            // undefined index warnings if people use this directly in the
            // template code, without checking its contents.
            $messages[$key]["new_count"] = '';

            // Skip moved messages in the count.
            if (isset($message["moved"]) && $message["moved"]) continue;

            // if the thread contains new messages
            if (isset($message['new']))
            {
                $count = array();

                // leave only unread messages to $count array
                if (is_array($message['meta']['message_ids']) &&
                    is_array($PHORUM['user']['newinfo'])) {
                    $count = array_diff(
                        $message['meta']['message_ids'],
                        $PHORUM['user']['newinfo']
                    );
                }

                $counter = 0;

                // check every messageid
                foreach ($count as $msgid)
                {
                    // Don't count messages smaller than min_id. These are
                    // all implicitly considered read by Phorum.
                    // Phorum 5.2 returns min_id differently than 5.1.
                    $forum_id = $message['forum_id'];
                    $min_id = is_array($PHORUM['user']['newinfo']['min_id'])
                            ? $PHORUM['user']['newinfo']['min_id'][$forum_id]
                            : $PHORUM['user']['newinfo']['min_id'];

                    if($msgid > $min_id) {
                        $counter++;
                    }
                }

                if ($counter > 0)
                {
                    // Find the language string to use.
                    $lang = $PHORUM["DATA"]["LANG"]["mod_new_count"];
                    if (isset($lang[$counter])) {
                        $langstr = $lang[$counter];
                    } elseif ($counter > 1) {
                        $langstr = $lang["multiple"];
                    } else {
                        $langstr = $lang["single"];
                    }
                    $newstr = str_replace('%count%', $counter, $langstr);

                    $messages[$key]["new"] = $newstr;
                    $messages[$key]["new_count"] = $counter;
                }
            }
        }
    }

    // Setup some data before the header is displayed.
    phorum_mod_new_count_before_header();

    return $messages;
}

function phorum_mod_new_count_index($forums)
{
    global $PHORUM;

    // We do not have new message counts if the user is not logged in.
    if (!$PHORUM["DATA"]["LOGGEDIN"]) return $forums;

    $PHORUM["DATA"]["NEW_MESSAGES"] = 0;
    $PHORUM["DATA"]["NEW_THREADS"] = 0;

    foreach ($forums as $forum)
    {
        if (!empty($forum["new_messages"])) {
            // For removing number formatting (Phorum 5.2).
            $new = preg_replace('/[^0-9]/', '', $forum["new_messages"]);
            $PHORUM["DATA"]["NEW_MESSAGES"] += $new;
        }

        if (!empty($forum["new_threads"])) {
            // For removing number formatting (Phorum 5.2).
            $new = preg_replace('/[^0-9]/', '', $forum["new_threads"]);
            $PHORUM["DATA"]["NEW_THREADS"] += $new;
        }
    }

    // Phorum 5.2 can format numbers.
    if (function_exists('number_format') && isset($PHORUM["thous_sep"])) {
        $PHORUM["DATA"]["NEW_MESSAGES"] = number_format($PHORUM["DATA"]["NEW_MESSAGES"], 0, $PHORUM["dec_sep"], $PHORUM["thous_sep"]);
        $PHORUM["DATA"]["NEW_THREADS"] = number_format($PHORUM["DATA"]["NEW_THREADS"], 0, $PHORUM["dec_sep"], $PHORUM["thous_sep"]);
    }

    // Setup data before the header is displayed.
    phorum_mod_new_count_before_header();

    return $forums;
}

function phorum_mod_new_count_before_header()
{
    $PHORUM = $GLOBALS["PHORUM"];

    // Do not display empty counts if hiding of that situation has
    // been enabled in the settings.
    if ((!isset($PHORUM["DATA"]["NEW_MESSAGES"]) ||
        !$PHORUM["DATA"]["NEW_MESSAGES"]) &&
        (isset($PHORUM["mod_new_count"]["hide_ifnonew"]) &&
         $PHORUM["mod_new_count"]["hide_ifnonew"])) {
        $GLOBALS["PHORUM"]["DATA"]["MOD_NEW_COUNT"] = '';
        return;
    }

    // Build HTML code for totals count.
    $counters =
        $PHORUM["DATA"]["LANG"]["mod_new_count"]["total_new_count"] .
        '&nbsp;<span class="PhorumNewFlag">' .
        $PHORUM["DATA"]["NEW_MESSAGES"] .
        '</span>&nbsp;' .
        $PHORUM["DATA"]["LANG"]["mod_new_count"]["total_new_threads"] .
        '&nbsp;<span class="PhorumNewFlag">' .
        $PHORUM["DATA"]["NEW_THREADS"] .
        '</span>&nbsp;';

    // Setup the data for the templates.
    $GLOBALS["PHORUM"]["DATA"]["MOD_NEW_COUNT"] = $counters;
}

function phorum_mod_new_count_after_header()
{
    $PHORUM = $GLOBALS["PHORUM"];

    // We do not have new message counts if the user is not logged in.
    if (!$PHORUM["DATA"]["LOGGEDIN"]) return;

    // Only display this on the index and list pages.
    if (phorum_page != 'index' && phorum_page != 'list') return;

    // If we have no forums at all, then the new count will not be set.
    if (!isset($PHORUM["DATA"]["MOD_NEW_COUNT"])) return;

    // Display the new count totals in the header, unless this has
    // been disabled in the settings.
    if (!isset($PHORUM["mod_new_count"]["hide_totals"]) ||
        !$PHORUM["mod_new_count"]["hide_totals"]) {
        print "<div align=\"right\">{$PHORUM["DATA"]["MOD_NEW_COUNT"]}</div>";
    }
}

?>
