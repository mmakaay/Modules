<?php

if(!defined("PHORUM")) return;

// Load the default settings information.
require_once('settings.default.php');

if (file_exists('./include/format_functions.php')) {
    require_once('./include/format_functions.php');
}

// Permission types, as used by check_topic_poll_permission()
define('TOPIC_POLL_ADD',                  1);
define('TOPIC_POLL_EDIT',                 2);
define('TOPIC_POLL_DELETE',               3);
define('TOPIC_POLL_SETSTATUS',            4);
define('TOPIC_POLL_CASTVOTE',             5);
define('TOPIC_POLL_REVOKEVOTE',           6);
define('TOPIC_POLL_VIEWRESULTS',          7);

// Return value for the check_if_topic_poll_is_open() function.
// The values for the constants are use for filling the
// mod_topic_poll_causes data.
define('TOPIC_POLL_CLOSED_NOPOLL',        'NoPollFound');
define('TOPIC_POLL_CLOSED_INACTIVE',      'PollIsInactive');
define('TOPIC_POLL_CLOSED_EXPIRED',       'PollIsExpired');
define('TOPIC_POLL_OPEN',                 'PollIsOpen');

// Return values for the check_if_topic_poll_vote_was_cast() function.
define('TOPIC_POLL_CAST_NOT',             0);
define('TOPIC_POLL_CAST_USER',            1);
define('TOPIC_POLL_CAST_COOKIE',          2);
define('TOPIC_POLL_CAST_IP',              3);

// This variable will be kept up to date with causes for various
// status situations.
$GLOBALS["PHORUM"]["mod_topic_poll_causes"] = array(
    "deny_vote"  => null,
    "deny_edit"  => null,
);

/**
 * Displays a button for adding a poll, if the user is allowed to
 * add a poll to the current message.
 */
function phorum_mod_topic_poll_tpl_editor_buttons()
{
    $PHORUM = $GLOBALS["PHORUM"];

    // Try to find the message data for the edited post.
    // Return if we cannot find it (shouldn't happen though).
    $message = null;
    // Phorum 5.2
    if (isset($PHORUM["DATA"]["POSTING"]))
        $message = $PHORUM["DATA"]["POSTING"];
    // Phorum 5.1
    else if (isset($PHORUM["DATA"]["POST"]))
        $message = $PHORUM["DATA"]["POST"];
    if ($message == NULL) return;

    // Check if the current user does have authorization for adding polls.
    if (! check_topic_poll_permission(TOPIC_POLL_ADD, $message)) return;

    // Display the add poll button.
    include phorum_get_template("topic_poll::addpoll_button");
}


/**
 * Handles the editing steps of a poll in the posting editor.
 *
 * @param $message The message data as composed by the posting script.
 * @return $message The (possibly modified) message data.
 */
function phorum_mod_topic_poll_posting_custom_action($message)
{
    global $PHORUM;

    // Module installation:
    // Load the module installation code if this was not yet done.
    // The installation code will take care of automatically adding
    // the custom profile field that is needed for this module.
    if (! isset($PHORUM["mod_topic_poll_installed"]) ||
        ! $PHORUM["mod_topic_poll_installed"]) {
        include("./mods/topic_poll/install.php");
    }

    // Keep track if we want to display the poll editor screen.
    $show_poll_editor = false;

    // Polls can only be in thread starting messages.
    if ($message["parent_id"]) return $message;

    // ADD A POLL
    if (! isset($message["meta"]["mod_topic_poll"]) &&
        isset($_POST["topic_poll:add"]) &&
        check_topic_poll_permission(TOPIC_POLL_ADD, $message)) {

        // Initialize a new poll.
        $message["meta"]["mod_topic_poll"] = array (
            "question"    => "",
            "answers"     => array( 0 => "", 1 => "" ),
            "votes"       => array(),
            "total_votes" => 0,
            "votingtime"  => 0, // in days, 0 for unlimited
            "permission"  => "user",
            "active"      => 1,
            "cache_id"    => 0,
        );

        $PHORUM["DATA"]["OKMSG"] =
            $PHORUM["DATA"]["LANG"]["mod_topic_poll"]["PollAdded"];

        $show_poll_editor = 1;
    }

    // ADD ANSWER FIELD
    if (isset($message["meta"]["mod_topic_poll"]) &&
        isset($_POST["topic_poll:add_answer"]) &&
        check_topic_poll_permission(TOPIC_POLL_EDIT, $message)) {

        $message["meta"]["mod_topic_poll"]["answers"][] = "";

        $show_poll_editor = 2;
    }

    // DELETE ANSWER FIELD
    // Make sure that at least two answers are kept for voting.
    if (isset($message["meta"]["mod_topic_poll"]) &&
        check_topic_poll_permission(TOPIC_POLL_EDIT, $message)) {
        foreach ($message["meta"]["mod_topic_poll"]["answers"] as $id => $answer) {
            if (isset($_POST["topic_poll:delete_answer:$id"]) &&

                count($message["meta"]["mod_topic_poll"]["answers"]) > 2) {
                unset($message["meta"]["mod_topic_poll"]["answers"][$id]);
                unset($message["meta"]["mod_topic_poll"]["votes"][$id]);
                $show_poll_editor = 3;
            }
        }
    }

    // DELETE THE POLL
    if (isset($message["meta"]["mod_topic_poll"]) &&
        isset($_POST["topic_poll:delete"]) &&
        check_topic_poll_permission(TOPIC_POLL_DELETE, $message)) {

        unset($message["meta"]["mod_topic_poll"]);

        $PHORUM["DATA"]["OKMSG"] =
            $PHORUM["DATA"]["LANG"]["mod_topic_poll"]["PollDeleted"];
    }

    // CHANGE POLL STATUS
    if (isset($message["meta"]["mod_topic_poll"]) &&
        (isset($_POST["topic_poll:activate"]) ||
         isset($_POST["topic_poll:deactivate"])) &&
         check_topic_poll_permission(TOPIC_POLL_SETSTATUS, $message)) {

         if (isset($_POST["topic_poll:activate"])) {
             $message["meta"]["mod_topic_poll"]["active"] = 1;
         } else {
             $message["meta"]["mod_topic_poll"]["active"] = 0;
         }
    }

    // CONFIGURATION CHANGE
    if (isset($message["meta"]["mod_topic_poll"]) &&
        check_topic_poll_permission(TOPIC_POLL_EDIT, $message)) {

        // Set the question.
        if (isset($_POST["topic_poll:question"])) {
            $message["meta"]["mod_topic_poll"]["question"] =
                trim($_POST["topic_poll:question"]);
        }

        // Set the answers.
        foreach ($message["meta"]["mod_topic_poll"]["answers"] as $id => $answer) {
            if (isset($_POST["topic_poll:answer:$id"])) {
                $message["meta"]["mod_topic_poll"]["answers"][$id] =
                    trim($_POST["topic_poll:answer:$id"]);
            }
        }

        // Set the votingtime.
        if (isset($_POST["topic_poll:votingtime"])) {
            $message["meta"]["mod_topic_poll"]["votingtime"] =
                abs((int) $_POST["topic_poll:votingtime"]);
        }

        // Set the permission.
        if (isset($_POST["topic_poll:permission"])) {
            $p = $_POST["topic_poll:permission"];
            if ($p == 'user' || $p == 'anonymous')
                $message["meta"]["mod_topic_poll"]["permission"] = $p;
        }

        // Set the no vote no read option.
        if (isset($_POST["topic_poll:novotenoread"])) {
            $message["meta"]["mod_topic_poll"]["novotenoread"] =
                empty($_POST["topic_poll:novotenoread"]) ? 0 : 1;
        }

        // Set the poll position.
        if (isset($_POST["topic_poll:position"])) {
            $p = $_POST["topic_poll:position"];
            if ($p == 'before' || $p == 'after')
                $message["meta"]["mod_topic_poll"]["position"] = $p;
        }
    }

    // Request to edit the poll. This is only used to switch to the
    // poll editor screen.
    if (isset($_POST["topic_poll:edit"]) &&
        check_topic_poll_permission(TOPIC_POLL_EDIT, $message)) {
        $show_poll_editor = 4;
    }

    // Request to go back to the posting editor. We check the
    // integrity of the posting data here, so we can stay at
    // the poll editor when there are problems with it.
    if (isset($_POST["topic_poll:back_to_message"]) &&
        check_topic_poll_permission(TOPIC_POLL_EDIT, $message)) {
        list ($message, $error) =
            phorum_mod_topic_poll_check_post(array($message, NULL));
        if ($error == NULL) {
            $PHORUM["DATA"]["OKMSG"] =
                $PHORUM["DATA"]["LANG"]["mod_topic_poll"]["BackToMessageHelp"];
        } else {
            $PHORUM["DATA"]["ERROR"] = $error;
            $show_poll_editor = 5;
        }
    }

    if ($show_poll_editor)
    {
        // The changed meta data must be stored in $_POST, so it will
        // be put in the form. Also sign it, to make Phorum accept it.
        $meta = base64_encode(serialize($message["meta"]));
        $_POST["meta"] = $meta;
        $_POST["meta:signature"] = phorum_generate_data_signature($meta);

        // Setup data for the templates.
        $poll = $message["meta"]["mod_topic_poll"];
        phorum_mod_topic_poll_setup_templatedata($poll, TRUE);

        // Create post data for remembering the editor state.
        $post_data = "";
        foreach ($_POST as $key => $val) {
            if ($key == "add_topic_poll") continue;
            if (substr($key, 0, 11) == "topic_poll:") continue;
            $post_data .= '<input type="hidden" ' .
                  'name="' . htmlspecialchars($key) . '" ' .
                  'value="' . htmlspecialchars($val) . "\" />\n";
        }
        $PHORUM["DATA"]["POST_VARS"] = $post_data;

        $PHORUM["DATA"]["POLL"]["CAN_DELETE"] =
            check_topic_poll_permission(TOPIC_POLL_DELETE, $message);

        $GLOBALS["PHORUM"]["posting_template"] = 'topic_poll::editor';
    }

    return $message;
}

/**
 * Displays poll information in the posting editor form.
 */
function phorum_mod_topic_poll_tpl_editor_before_textarea()
{
    global $PHORUM;

    // Try to find the message data for the edited post.
    // Return if we cannot find it (shouldn't happen though).
    $message = null;
    // Phorum 5.2
    if (isset($PHORUM["DATA"]["POSTING"]))
        $message = $PHORUM["DATA"]["POSTING"];
    // Phorum 5.1
    else if (isset($PHORUM["DATA"]["POST"]))
        $message = $PHORUM["DATA"]["POST"];
    if ($message == NULL) return;

    // No poll available? Then we're done.
    if (! isset($message["meta"]["mod_topic_poll"])) return;

    $poll = $message["meta"]["mod_topic_poll"];
    phorum_mod_topic_poll_setup_templatedata($poll);

    $PHORUM["DATA"]["POLL"]["CAN_EDIT"] =
        check_topic_poll_permission(TOPIC_POLL_EDIT, $message);
    $PHORUM["DATA"]["POLL"]["CAN_SETSTATUS"] =
        check_topic_poll_permission(TOPIC_POLL_SETSTATUS, $message);
    $PHORUM["DATA"]["POLL"]["CAN_DELETE"] =
        check_topic_poll_permission(TOPIC_POLL_DELETE, $message);

    // Determine if we have to show some special message.
    $statusmessage = null;
    if (isset($PHORUM["mod_topic_poll_causes"]["deny_edit"])) {
        $deny_reason = $PHORUM["mod_topic_poll_causes"]["deny_edit"];
        $lang = $GLOBALS["PHORUM"]["DATA"]["LANG"]["mod_topic_poll"];
        if (isset( $lang[$deny_reason])) {
            $statusmessage = $lang[$deny_reason];
        } else {
            $statusmessage = "*** no string for deny edit reason: $deny_reason";
        }
    }
    $PHORUM["DATA"]["POLL"]["STATUSMESSAGE"] = $statusmessage;

    include phorum_get_template("topic_poll::before_textarea_pollinfo");
}

/**
 * Checks the integrity of the poll data that is being submitted.
 *
 * @param $data An array containing the message data array and an error field.
 * @return $data Possibly changed input $data. If an error is encountered,
 *         then the error field will be set.
 */
function phorum_mod_topic_poll_check_post($data)
{
    list ($message, $error) = $data;

    // If a previous mod produced an error, we pass on that error.
    // If there are problems with the data for this mod, we'll get
    // our chance to complain about it later.
    if (!empty($error)) return $data;

    // If we have no poll, there's nothing to check.
    if (!isset($message["meta"]["mod_topic_poll"])) return $data;

    $poll = $message["meta"]["mod_topic_poll"];
    $lang = $GLOBALS["PHORUM"]["DATA"]["LANG"]["mod_topic_poll"];

    // Check the poll question.
    if (trim($poll["question"]) == "") {
        return array($message, $lang["ErrorQuestion"]);
    }

    // Check the poll answers.
    $nr = 0;
    foreach ($poll["answers"] as $id => $answer) {
        $nr++;
        if (trim($answer) == "") {
            $error = str_replace("%nr%", $nr, $lang["ErrorAnswer"]);
            return array($message, $error);
        }
    }

    // This shouldn't happen, but let's check it anyway.
    if ($nr < 2) {
        return array($message, $lang["ErrorAnswerCount"]);
    }

    // All seems to be okay. Upgrade the cache_id, so we have a good
    // identifier for caching generated output.
    $message["meta"]["mod_topic_poll"]["cache_id"] ++;

    return array($message, $error);
}

/*
 * Handles voting actions on the poll.
 */
function phorum_mod_topic_poll_read($messages)
{
    global $PHORUM;

    // To satisfy the CSRF check that the posting form uses on the
    // same page as our topic poll form. This will add a posting
    // token to the {POST_VARS}. The forms that we use have the
    // {POST_VARS} in them, so when posting a poll form, the CSRF
    // check from posting.php will succeed.
    //
    // Although the script is handled from the read page, we use
    // "post" here and not "read". That is because the read page
    // does not do a CSRF check of its own, but the included
    // posting.php script does (using "post" as the page name.)
    //
    // The function_exists is for making this work in older versions
    // of Phorum as well.
    if (function_exists('phorum_check_posting_token')) {
        phorum_check_posting_token('post');
    }

    // Get the first message and see if we need to handle a poll
    // vote or vote revocation.
    reset($messages);
    list ($message_id, $message) = each($messages);
    $handle = false;
    if ($message["parent_id"] == 0 &&
        isset($message["meta"]["mod_topic_poll"]) &&
        (isset($_POST["topic_poll:revoke_vote"]) ||
         isset($_POST["topic_poll:vote"]))) $handle = true;
    if (! $handle) {
        return topic_poll_novotenoread($messages, $message);
    }

    $poll = $message["meta"]["mod_topic_poll"];

    // Fix if datestamp was overwritten with a formatted date (Phorum 5.1).
    if (! is_numeric($message["datestamp"])) {
        $dbmessage = phorum_db_get_message($message["message_id"]);
        if (! $dbmessage) return; // should not happen
        $message["raw_datestamp"] = $dbmessage["datestamp"];
    } else {
        $message["raw_datestamp"] = $message["datestamp"];
    }

    // Handle user action: revoke a vote.
    if (isset($_POST["topic_poll:revoke_vote"]) &&
        check_topic_poll_permission(TOPIC_POLL_REVOKEVOTE, $message)) {

        // Retrieve the poll voting info for the current user.
        $data = array();
        if (isset($PHORUM["user"]["mod_topic_poll"]) &&
            is_array($PHORUM["user"]["mod_topic_poll"])) {
            $data = $PHORUM["user"]["mod_topic_poll"];
        }

        // Should be always true for logged in users who can revoke a vote.
        if (isset($data[$message["msgid"]]))
        {
            // Find the answer that the user gave.
            $answer = $data[$message["msgid"]];

            // Remove the poll as being answered from the user data.
            unset($data[$message["msgid"]]);
            if (function_exists('phorum_api_user_save')) {
                // 5.2
                phorum_api_user_save(array(
                    "user_id" => $PHORUM["user"]["user_id"],
                    "mod_topic_poll" => $data
                ));
            } else {
                // 5.1
                phorum_user_save(array(
                    "user_id" => $PHORUM["user"]["user_id"],
                    "mod_topic_poll" => $data
                ));
            }

            // Update the poll counters.
            $poll["votes"][$answer] --;
            if ($poll["votes"][$answer] < 0) // should not happen
                $poll["votes"][$answer] = 0;
            $poll["cache_id"] ++;
            $total = 0;
            foreach ($poll["votes"] as $id => $count)
                $total += $count;
            $poll["total_votes"] = $total;
            $message["meta"]["mod_topic_poll"] = $poll;
            phorum_db_update_message(
                $message["message_id"],
                array("meta" => $message["meta"])
            );

            // Flush message cache.
            if (isset($PHORUM['cache_messages']) && $PHORUM['cache_messages']) {
                phorum_cache_remove("message", $message["message_id"]);
            }

            // Update the vote cookie.
            if (isset($_COOKIE["phorum_mod_topic_poll"])) {
                $voted = explode(":", $_COOKIE["phorum_mod_topic_poll"]);
                $new = array();
                foreach ($voted as $id) {
                    if ($id !== $message["message_id"]) {
                        $new[] = $id;
                    }
                }
                $_COOKIE["phorum_mod_topic_poll"] = implode(":", $new);
                setcookie(
                    "phorum_mod_topic_poll",
                    $_COOKIE["phorum_mod_topic_poll"],
                    time() + 86400 * 365 // 1 year
                );
            }

            $messages[$message_id] = $message;
        }
    }

    // Handle user action: casting a vote.
    if (isset($_POST["topic_poll:cast_vote"]) &&
        isset($_POST["topic_poll:vote"]) &&
        check_topic_poll_permission(TOPIC_POLL_CASTVOTE, $message)) {

        // See if the answer that the user chose is valid for the poll.
        $answer = $_POST["topic_poll:vote"];
        if (isset($poll["answers"][$answer]))
        {
            // All is okay. Handle the vote. First update the
            // poll information in the message meta data.
            $poll["total_votes"] ++;
            if (! isset($poll["votes"][$answer])) $poll["votes"][$answer] = 0;
            $poll["votes"][$answer] ++;
            $poll["cache_id"] ++;
            $message["meta"]["mod_topic_poll"] = $poll;
            $update = array("meta" => $message["meta"]);
            phorum_db_update_message($message["message_id"], $update);

            // Flush message cache, so the updates will be visible.
            if (isset($PHORUM['cache_messages']) && $PHORUM['cache_messages']) {
                phorum_cache_remove("message", $message["message_id"]);
            }

            // Do vote tracking for registered users.
            if ($PHORUM["DATA"]["LOGGEDIN"])
            {
                $data = array();
                if (isset($PHORUM["user"]["mod_topic_poll"]) &&
                    is_array($PHORUM["user"]["mod_topic_poll"])) {
                    $data = $PHORUM["user"]["mod_topic_poll"];
                }
                $data[$message["msgid"]] = $answer;
                if (function_exists('phorum_api_user_save')) {
                    // 5.2
                    phorum_api_user_save(array(
                        "user_id" => $PHORUM["user"]["user_id"],
                        "mod_topic_poll" => $data
                    ));
                } else {
                    // 5.1
                    phorum_user_save(array(
                        "user_id" => $PHORUM["user"]["user_id"],
                        "mod_topic_poll" => $data
                    ));
                }
            }
            // Setup IP-address blocking for anonymous users.
            else
            {
                // Retrieve the ip blocktime setting for the current forum
                $settings = phorum_mod_topic_poll_get_forumsettings();
                $ip_blocktime = $settings["ip_blocktime"];

                phorum_cache_put(
                    'mod_topic_poll_ipblock',
                    $_SERVER["REMOTE_ADDR"] . ":" . $message["message_id"],
                    true,
                    $ip_blocktime * 60
                );
            }

            // All users get a cookie for remembering the vote.
            $voted = array();
            if (isset($_COOKIE["phorum_mod_topic_poll"])) {
                $voted = explode(":", $_COOKIE["phorum_mod_topic_poll"]);
            }
            $voted[] = $message["message_id"];
            $_COOKIE["phorum_mod_topic_poll"] = implode(":", $voted);
            setcookie(
                "phorum_mod_topic_poll",
                implode(":", $voted),
                time() + 86400 * 365 // 1 year
            );

            $messages[$message_id] = $message;
        }
    }

    return topic_poll_novotenoread($messages, $message);
}

/**
 * Handle modifying the read hook message list for the "no vote no read"
 * poll option. The calling code has to check whether this feature has
 * to be used or not.
 */
function topic_poll_novotenoread($messages, $thread)
{
    global $PHORUM;

    // Check if the no vote no read option needs to be handled.
    if (!isset($thread['meta']['mod_topic_poll']) ||
        empty($thread['meta']['mod_topic_poll']['novotenoread'])) {
        return $messages;
    }

    // If a vote was cast, then no action is needed.
    if (check_if_topic_poll_vote_was_cast($thread)) {
        return $messages;
    }

    // Only one message in this thread? Then let's not bother the user.
    if ($thread['thread_count'] <= 0) return $messages;

    // Format the notification.
    $msg = $PHORUM['DATA']['LANG']['mod_topic_poll']['ReadThreadNeedsVote'];
    $msg = str_replace('%count%', $thread['thread_count'], $msg);

    // Remember it for the formatting function.
    $thread['topic_poll_novotenoread'] = $msg;

    // Shrink the messages array to only contain the thread starter.
    $messages = array( $thread['message_id'] => $thread );

    return $messages;
}

/**
 * Puts the visitor poll view in the message body of the first message
 * in the thread, in case that message contains a poll.
 */
function phorum_mod_topic_poll_format($messages, $admin_preview = false)
{
    global $PHORUM;

    // Retrieve the poll settings for the current forum.
    $settings = phorum_mod_topic_poll_get_forumsettings();

    foreach ($messages as $message_id => $message)
    {
        // Polls can only be on thread starter messages.
        // And if parent_id is not set, the format function from the
        // control center is probably called to format the user's
        // signature, so then there's also no need for running this hook.
        if (!isset($message["parent_id"]) || $message["parent_id"]) continue;

        // No poll in the thread starter message?
        if (!isset($message["meta"]["mod_topic_poll"]) ||
            !is_array($message["meta"]["mod_topic_poll"])) {

            // 2 = the setting for filling template vars on the list page.
            // We set the topic_poll variable to zero to indicate "no poll".
            if ($settings["subjecttag"] == 2 && phorum_page != "read") {
                $messages[$message_id]['topic_poll'] = 0;
            }

            continue;
        }

        // Rendering for a poll in a feed (e.g. RSS or ATOM).
        if (phorum_page == "feed")
        {
            $poll =
              "<b>".$PHORUM["DATA"]["LANG"]["mod_topic_poll"]["Poll"]."</b>: ".
              htmlspecialchars($message["meta"]["mod_topic_poll"]["question"]);

            $messages[$message_id]["body"] =
                (isset($message["meta"]["mod_topic_poll"]["position"]) &&
                 $message["meta"]["mod_topic_poll"]["position"] == 'after')
                ? $messages[$message_id]["body"] . "<br/><br/>$poll"
                : "$poll <br/><br/>" . $messages[$message_id]["body"];
            continue;
        }

        // Handle preview setup.
        if (empty($message["message_id"])) {
            $message["datestamp"] = time();
        }

        // Add a tag to the subject or setup template variables (based on
        // the poll admin settings). We do this on pages other than the
        // read page to point out to users that there's a poll in the
        // message.
        if ($settings["subjecttag"] && phorum_page != "read") {
            $tag = "OldPollSubjectTag";
            $var = 1;
            if ($settings["subjecttag_marknew"] &&
                check_topic_poll_permission(TOPIC_POLL_CASTVOTE, $message)) {
                $tag = "NewPollSubjectTag";
                $var = 2;
            }

            if ($settings['subjecttag'] == 2) {
                $messages[$message_id]["topic_poll"] = $var;
            } else {
                $messages[$message_id]["subject"] .=
                    $PHORUM["DATA"]["LANG"]["mod_topic_poll"][$tag];
            }
        }

        // No body to format (e.g. on the list page), then we're done.
        if (!isset($message["body"])) continue;

        // Find the raw datestamp. In 5.2 this one is already available
        // as raw_datestamp. In 5.1 we have to lookup this one, because
        // the datestamp field was formatted and the original datestamp
        // is no longer available.
        if (! isset($message["raw_datestamp"])) {
            $orig_forum_id = $PHORUM["forum_id"];
            $PHORUM["forum_id"] = $message["forum_id"];
            $dbmessage = phorum_db_get_message($message["message_id"]);
            $PHORUM["forum_id"] = $orig_forum_id;
            if (! $dbmessage) continue; // should not happen
            $message["raw_datestamp"] = $dbmessage["datestamp"];
        }

        $poll = $message["meta"]["mod_topic_poll"];

        // Find poll permissions for the current user.
        $vote_ok = check_topic_poll_permission(TOPIC_POLL_CASTVOTE, $message);
        $revoke_ok = check_topic_poll_permission(TOPIC_POLL_REVOKEVOTE, $message);
        $viewresults_ok = check_topic_poll_permission(TOPIC_POLL_VIEWRESULTS, $message);

        $msgid = isset($message["msgid"]) ? $message["msgid"] : 0;
        $my_answer = $revoke_ok ? $PHORUM["user"]["mod_topic_poll"][$msgid] : null;

        // Setup template data.
        phorum_mod_topic_poll_setup_templatedata($poll);
        $PHORUM["DATA"]["POLL"]["PREVIEW"] = empty($message["message_id"]);
        $PHORUM["DATA"]["POLL"]["CAN_VOTE"] = $vote_ok;
        $PHORUM["DATA"]["POLL"]["CAN_REVOKE"] = $revoke_ok;
        $PHORUM["DATA"]["POLL"]["CAN_VIEWRESULTS"] = $viewresults_ok;
        $PHORUM["DATA"]["POLL"]["CURRENT_VOTE"] = $my_answer;
        $PHORUM["DATA"]["POLL"]["NOVOTENOREAD"] =
            isset($message['topic_poll_novotenoread']) ?
            $message['topic_poll_novotenoread'] : '';
        $PHORUM["DATA"]["POLL"]["POST_URL"] = $message["URL"]["READ"];

        // Render the poll data to display in the posting.
        $lang = $PHORUM["DATA"]["LANG"]["mod_topic_poll"];
        $rendered_poll = '';
        ob_start();

        // Determine if the voting form should be shown.
        $show_voting_form = true;
        if (! $vote_ok) {
            $show_voting_form = false;
        }
        else if ($viewresults_ok && isset($_POST["topic_poll:view_results"])) {
            $show_voting_form = false;
        }

        // The user can vote, so show the voting form.
        if ($show_voting_form)
        {
            // The number of votes so far.
            $str = $lang["NumberOfVotes"];
            $str = str_replace("%votes%", $poll["total_votes"], $str);
            $PHORUM["DATA"]["POLL"]["TOTAL_VOTES"] = $poll["total_votes"];
            $PHORUM["DATA"]["POLL"]["TOTAL_VOTES_STR"] = $str;

            // The endtime for the poll.
            if ($poll["active"] && $poll["votingtime"])
            {
                // Compute and format the closing time.
                $datestamp = $message["raw_datestamp"];
                $endtime = $datestamp + ($poll["votingtime"] * 86400);
                if ($endtime > time()) {
                    $format = isset($GLOBALS["PHORUM"]['short_date_time'])
                            ? $GLOBALS["PHORUM"]['short_date_time'] // Phorum 5.2
                            : $GLOBALS["PHORUM"]['short_date'];     // Phorum 5.1
                    $endtime = phorum_date($format, $endtime);
                    $str = str_replace("%time%", $endtime, $lang["VotingEndsAt"]);
                }
                $PHORUM["DATA"]["POLL"]["VOTING_ENDTIME"] = $str;
            }

            // A description of the permission for the poll.
            $str = $poll["permission"] == "user"
                 ? $lang["VotingUsersOnly"]
                 : $lang["VotingOpenForAll"];
            $PHORUM["DATA"]["POLL"]["PERMISSION_STR"] = $str;

            include phorum_get_template("topic_poll::voting_form");
        }
        // Show the voting results.
        else
        {
            // Compute result statistics.
            $percentages = array();
            $total_perc = 0;
            $highest_perc = 0;
            $total_votes = $poll["total_votes"];
            foreach ($poll["answers"] as $id => $answer)
            {
                $count = isset($poll["votes"][$id]) ? $poll["votes"][$id] : 0;
                $perc = $total_votes ? floor($count / $total_votes * 100 + 0.5) : 0;
                if (($total_perc + $perc) > 100) {
                    $perc = 100 - $total_perc;
                }
                $total_perc += $perc;
                if ($perc > $highest_perc) $highest_perc = $perc;
                $percentages[$id] = $perc;
            }

            // Compute relative percentages for displaying the result bars.
            // The highest percentage must be 95% wide (100% is not used,
            // so there's some extra room for styling the bars).
            $barwidths = array();
            foreach ($percentages as $id => $perc) {
                $barwidths[$id] = $highest_perc ? floor($perc/$highest_perc*95 + 0.5) : 0;
            }

            // Determine if we have to show some special message.
            $statusmessage = null;
            if (isset($PHORUM["mod_topic_poll_causes"]["deny_vote"])) {
                $deny_reason = $PHORUM["mod_topic_poll_causes"]["deny_vote"];
                if (isset( $lang[$deny_reason])) {
                    $statusmessage = $lang[$deny_reason];
                } else {
                    $statusmessage = "*** no string for deny vote reason: $deny_reason";
                }
            }

            // Display the number of votes so far.
            $str = $lang["NumberOfVotes"];
            $str = str_replace("%votes%", $poll["total_votes"], $str);
            if ($statusmessage != null) $statusmessage .= "<br/>";
            $statusmessage .= $str;

            // Put the results in the template data.
            foreach ($PHORUM["DATA"]["POLL"]["ANSWERS"] as $id => $answer) {
                $answerid = $answer["ID"];
                $PHORUM["DATA"]["POLL"]["ANSWERS"][$id]["PERCENTAGE"] =
                    $percentages[$answerid] ."%";
                $PHORUM["DATA"]["POLL"]["ANSWERS"][$id]["BARWIDTH"] =
                    $barwidths[$answerid] ."%";
            }

            $PHORUM["DATA"]["POLL"]["TOTAL_VOTES"] = $total_votes;
            $PHORUM["DATA"]["POLL"]["STATUSMESSAGE"] = $statusmessage;
            $PHORUM["DATA"]["POLL"]["ADMIN_PREVIEW"] = $admin_preview;

            include phorum_get_template("topic_poll::voting_results");
        }

        $rendered_poll = ob_get_contents();
        ob_end_clean();

        // The separator between the poll and body.
        ob_start();
        include phorum_get_template('topic_poll::body_and_poll_separator');
        $separator = ob_get_contents();
        ob_end_clean();

        $placeholder = '[mod_topic_poll '.md5($rendered_poll).']';
        $PHORUM['mod_topic_poll']['format_fixup'][$message_id][$placeholder] =
            $rendered_poll;

        // Add the rendered poll to the body.
        $messages[$message_id]['body'] =
            (isset($message['meta']['mod_topic_poll']['position']) &&
             $message['meta']['mod_topic_poll']['position'] == 'after')

            ? $messages[$message_id]['body'] .
              $separator .
              $placeholder .
              '<br/>'

            : $placeholder .
              $separator .
              $messages[$message_id]["body"];
    }

    return $messages;
}

function phorum_mod_topic_poll_format_fixup($messages)
{
    global $PHORUM;

    if (!empty($PHORUM['mod_topic_poll']['format_fixup'])) {
        $format_fixup = $PHORUM['mod_topic_poll']['format_fixup'];
        foreach ($format_fixup as $id => $fixes) {
            foreach ($fixes as $placeholder => $content) {
                if (isset($messages[$id]['body'])) {
                    $messages[$id]['body']
                        = str_replace($placeholder, $content, $messages[$id]['body']);
                }
            }
        }
    }

    return $messages;
}

/**
 * Sets up the general poll information for use in templates.
 *
 * @param $poll The poll data.
 */
function phorum_mod_topic_poll_setup_templatedata($poll, $for_form = FALSE)
{
    global $PHORUM;

    // Retrieve the poll settings for the current forum.
    $settings = phorum_mod_topic_poll_get_forumsettings();

    // Create XSS safe data for the templates. This is done
    // in a very defensive way, because I'm paranoid.

    $answers = array();
    $nr = 0;
    foreach ($poll["answers"] as $id => $answer) {
        if ($for_form) {
          $answer = htmlspecialchars($answer);
        } else {
          $formatted = phorum_format_messages(array(
            'temp' => array('body' => $answer))
          );
          $answer = $formatted['temp']['body'];
        }
        $nr++;
        $answers[] = array(
            "NUMBER" => $nr,
            "ID"     => (int)$id,
            "ANSWER" => $answer,
            "VOTES"  => isset($poll["votes"][$id])
                        ? (int)$poll["votes"][$id] : 0
        );
    }

    if ($for_form) {
      $question = htmlspecialchars($poll['question']);
    } else {
      $formatted = phorum_format_messages(
          array('temp' => array('body' => $poll['question'])));
      $question = $formatted['temp']['body'];
    }

    $PHORUM["DATA"]["POLL"] = array(
        "QUESTION"           => $question,
        "ANSWERS"            => $answers,
        "ANSWER_COUNT"       => $nr,
        "CAN_DELETE_ANSWERS" => $nr > 2,
        "VOTE_COUNT"         => (int)$poll["total_votes"],
        "VOTINGTIME"         => (int)$poll["votingtime"],
        "PERMISSION"         => htmlspecialchars($poll["permission"]),
        "NOVOTENOREAD"       => empty($poll["novotenoread"]) ? 0 : 1,
        "POSITION"           => isset($poll["position"])
                                ? htmlspecialchars($poll["position"])
                                : "before",
        "ACTIVE"             => (int)$poll["active"],
        "CACHE_ID"           => (int)$poll["cache_id"]
    );

    // Since we are setting up poll template information, we probably
    // are doing poll stuff on the current screen. Put the style sheet
    // information in the page header. Make sure that this is only
    // done once, because this function could be called multiple times.
    if (isset($PHORUM["mod_topic_poll_stylesheets_added"])) return;
    $style = $settings["style"];

    // Add the generic stylsheet and then the specific style to the page
    // header data. The specific style is the one that can be used to
    //override the default style.
    $PHORUM["DATA"]["HEAD_TAGS"] .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$PHORUM["http_path"]}/mods/topic_poll/topic_poll.css\"/>\n<link rel=\"stylesheet\" type=\"text/css\" href=\"{$PHORUM["http_path"]}/mods/topic_poll/styles/" . htmlspecialchars($style) . "/style.css\"/>";

    $PHORUM["mod_topic_poll_stylesheets_added"] = true;
}

/**
 * Checks if the current user has some permission for managing a poll
 * on the given message.
 * @param $type The type of permission that is requested; one of
 *        TOPIC_POLL_ADD, TOPIC_POLL_EDIT, TOPIC_POLL_DELETE,
 *        TOPIC_POLL_SETSTATUS, TOPIC_POLL_CASTVOTE,
 *        TOPIC_POLL_REVOKEVOTE.
 * @param $message The message data for the message.
 * @return True if the permission is granted, false otherwise.
 */
function check_topic_poll_permission($type, $message)
{
    global $PHORUM;

    // Messages that aren't thread starters never can have a
    // topic poll on them. So for those, there can never be
    // a valid permission.
    if (! isset($message["parent_id"]) || $message["parent_id"] != 0)
        return false;

    // Get the poll data from the message, if available.
    $poll = null;
    if (isset($message["meta"]["mod_topic_poll"])) {
        $poll = $message["meta"]["mod_topic_poll"];
    }

    // Retrieve the poll settings for the current forum.
    $settings = phorum_mod_topic_poll_get_forumsettings();

    $userdata = $PHORUM["DATA"]["LOGGEDIN"] ? $PHORUM["user"] : null;

    switch ($type)
    {
        case TOPIC_POLL_ADD:

            // Only thread starting messages can get a poll.
            if ($message["parent_id"] != 0)
                return false;

            // A poll can't be added if one is already available (duh!).
            if (isset($message["meta"]["mod_topic_poll"]))
                return false;

            // Check the forum poll permission (can be configured from
            // the module settings screen).
            switch ($settings["permission"]) {
                case 1: return true;
                case 2: return $PHORUM["DATA"]["LOGGEDIN"];
                case 3: return $PHORUM["DATA"]["MODERATOR"];
                case 4: return $PHORUM["user"]["admin"];
                default: return false;
            }

        case TOPIC_POLL_EDIT:

            $PHORUM["mod_topic_poll_causes"]["deny_edit"] = NULL;

            // If the poll is in use (votes have been cast), then
            // the user can no longer edit it. Only moderators
            // can do that from then on.
            if ($poll != null && $poll["total_votes"] > 0) {
                $is_mod = isset($PHORUM["DATA"]["MODERATOR"]) &&
                          $PHORUM["DATA"]["MODERATOR"];
                if ($is_mod) {
                    return true;
                } else {
                    $PHORUM["mod_topic_poll_causes"]["deny_edit"] = 'NoEditAfterVotes';
                    return false;
                }
            }

            return true;

        case TOPIC_POLL_DELETE:

            // Currently, the same rules as for TOPIC_POLL_EDIT apply here.
            return check_topic_poll_permission(TOPIC_POLL_EDIT, $message);

        case TOPIC_POLL_SETSTATUS:

            // Currently, the user can always deactivate/activate his polls.
            return true;

        case TOPIC_POLL_CASTVOTE:

            $PHORUM["mod_topic_poll_causes"]["deny_vote"] = NULL;

            // No voting if the poll is closed.
            $check = check_if_topic_poll_is_open($message);
            if ($check != TOPIC_POLL_OPEN) {
                $PHORUM["mod_topic_poll_causes"]["deny_vote"] = $check;
                return false;
            }

            // Anonymous users can only vote in case the permission
            // level is set to "anonymous".
            if ($userdata == NULL && $poll["permission"] != "anonymous") {
                $PHORUM["mod_topic_poll_causes"]["deny_vote"] = "DenyAnonymous";
                return false;
            }

            // Visitors can't vote if they already cast a vote before.
            $vote_already_cast = check_if_topic_poll_vote_was_cast($message);
            if ($vote_already_cast) {
                $PHORUM["mod_topic_poll_causes"]["deny_vote"] = "AlreadyVoted";
                return false;
            }

            // All checks passed! The visitor is allowed to cast a vote.
            return true;

        case TOPIC_POLL_REVOKEVOTE:

            // No revoking if this isn't allowed from the module settings.
            if (! $settings["allow_revoke"]) {
                return false;
            }

            // No revoking if the poll is closed.
            if (check_if_topic_poll_is_open($message) != TOPIC_POLL_OPEN) {
                return false;
            }

            // Registered users who have cast a vote can revoke it.
            $vote_already_cast = check_if_topic_poll_vote_was_cast($message);
            if ($vote_already_cast == TOPIC_POLL_CAST_USER) {
                return true;
            }

            return false;

        case TOPIC_POLL_VIEWRESULTS:

            // Moderators always can view the results without voting.
            $is_mod = isset($PHORUM["DATA"]["MODERATOR"]) &&
                      $PHORUM["DATA"]["MODERATOR"];
            if ($is_mod) return true;

            // For other users, the admin option for viewing must be enabled.
            return $settings["allow_novoteview"];

        default:
            die("Internal error in check_topic_poll_permission(): " .
                "unknown permission type: " . htmlspecialchars($type));
    }
}

/**
 * Checks if a poll is open for voting.
 *
 * @param $message The message with a poll in it, to check for being open.
 * @return One of TOPIC_POLL_CLOSED_NOPOLL, TOPIC_POLL_CLOSED_INACTIVE and
 *         TOPIC_POLL_CLOSED_EXPIRED if the poll is closed for some reason
 *         and TOPIC_POLL_OPEN if the poll is open.
 */
function check_if_topic_poll_is_open($message)
{
    // If the topic is closed, then the poll is deactivated.
    if (isset($message["closed"]) && $message["closed"]) {
        return TOPIC_POLL_CLOSED_INACTIVE;
    }

    // Huh, no poll data? Then the poll cannot be open :)
    if (! isset($message["meta"]["mod_topic_poll"])) {
        return TOPIC_POLL_CLOSED_NOPOLL;
    }

    $poll = $message["meta"]["mod_topic_poll"];

    // Nobody can vote if the poll is deactivated.
    if ($poll["active"] == 0) {
        return TOPIC_POLL_CLOSED_INACTIVE;
    }

    // Nobody can vote if the voting time is over.
    if ($poll["votingtime"] > 0) {

        // Fix if datestamp was overwritten with a formatted date (Phorum 5.1).
        if (! is_numeric($message["datestamp"])) {
            $dbmessage = phorum_db_get_message($message["message_id"]);
            if (! $dbmessage) return; // should not happen
            $message["raw_datestamp"] = $dbmessage["datestamp"];
        } else {
            $message["raw_datestamp"] = $message["datestamp"];
        }

        $closetime = $message["raw_datestamp"] + $poll["votingtime"]*86400;
        if ($closetime < time()) {
            return TOPIC_POLL_CLOSED_EXPIRED;
        }
    }

    return TOPIC_POLL_OPEN;
}

/**
 * Checks if a vote was already cast for the poll in the message.
 * It will check if the vote cookie is available. If this is the case,
 * voting is denied. For anonymous users, voting twice from the same
 * IP address within the IP blocking time is denied as well.
 * For registered users, the cast votes are stored in the user
 * profile. So for those users, the vote is checked in there too.
 *
 * @param $message The message with a poll in it, to check for voting.
 * @return NULL in case there is no poll in the message.
 *         TOPIC_POLL_CAST_USER if a registered user cast a vote.
 *         TOPIC_POLL_CAST_COOKIE if there was a voting cookie set for the poll.
 *         TOPIC_POLL_CAST_IP if there was a voting IP block set for the poll.
 *         TOPIC_POLL_CAST_NOT if there was not yet a vote casted.
 *         Note that TOPIC_POLL_CAST_NOT evaluates as false and the other
 *         TOPIC_POLL_CAST_* definitions evaluate as true.
 *
 */
function check_if_topic_poll_vote_was_cast($message)
{
    $PHORUM = $GLOBALS["PHORUM"];
    $user = $PHORUM["DATA"]["LOGGEDIN"] ? $PHORUM["user"] : NULL;

    // No msg_id set? This might be a new message.
    if (!isset($message["msgid"])) return NULL;
    $msgid = $message["msgid"];

    // No poll in the current message?
    if (!isset($message["meta"]["mod_topic_poll"])) return NULL;
    $poll = $message["meta"]["mod_topic_poll"];

    // If the user is logged in, then check in the profile data if
    // a vote was cast. Also check if the vote is valid for the poll.
    // Users could have cast a vote for an option that was deleted
    // afterwards. They should get a chance to re-cast a vote.
    // In this check, we use the unique msgid field as the poll id.
    // That way the vote won't be lost when for some reason the
    // message_id changes it value.
    if ($user && isset($user["mod_topic_poll"])) {
        $voteinfo = $user["mod_topic_poll"];
        if (isset($voteinfo[$msgid])) {
            $voted = $voteinfo[$msgid];
            if (isset($poll["answers"][$voted])) {
                return TOPIC_POLL_CAST_USER;
            }
        }
    }

    // Checks for anonymous users.
    if (! $user)
    {
        // Check if there is a voting cookie set for this poll. For
        // these cookies we use the message_id instead of the unique
        // string based msg_id, because it takes less space. This could
        // enable voting for the poll if the message_id changes (due to
        // moderator actions for example), but since cookie based
        // blocking isn't that secure in the first place, it's okay
        // to go with that.
        if (isset($_COOKIE["phorum_mod_topic_poll"])) {
            $voted = explode(":", $_COOKIE["phorum_mod_topic_poll"]);
            if (in_array($message["message_id"], $voted)) {
                return TOPIC_POLL_CAST_COOKIE;
            }
        }

        // Check if an anonymous user has voted for this poll before
        // from the same IP address within the IP blocking time.
        // Just like with cookies, we use the message_id here as well.
        $locked = phorum_cache_get(
            'mod_topic_poll_ipblock',
            $_SERVER["REMOTE_ADDR"] . ":" . $message["message_id"]
        );
        if ($locked) return TOPIC_POLL_CAST_IP;
    }

    return TOPIC_POLL_CAST_NOT;
}

/**
 * Returns the poll settings for the current forum or the forum
 * id that is passed as an argument. The default forum settings
 * will be applied for missing settings.
 *
 * @param $forum_id The forum id to retrieve the settings for or
 *        NULL if the settings for the current forum must be
 *        retrieved.
 * @return $settings An array containing the settings for the forum.
 */
function phorum_mod_topic_poll_get_forumsettings($forum_id = NULL)
{
    $PHORUM = $GLOBALS["PHORUM"];

    if ($forum_id == NULL) {
        $forum_id = $PHORUM["forum_id"];
    }

    if (isset($PHORUM["mod_topic_poll_settings_cache"][$forum_id])) {
        return $PHORUM["mod_topic_poll_settings_cache"][$forum_id];
    }

    $settings = isset($PHORUM["mod_topic_poll"][$forum_id])
              ? $PHORUM["mod_topic_poll"][$forum_id] : array();
    foreach ($PHORUM["mod_topic_poll_defaults"] as $key => $val) {
        if (!isset($settings[$key])) {
            $settings[$key] = $val;
        }
    }

    $GLOBALS["PHORUM"]["mod_topic_poll_settings_cache"][$forum_id] = $settings;
    return $settings;
}

?>
