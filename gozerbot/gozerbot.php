<?php

if (!defined('PHORUM')) return;

include('./mods/gozerbot/defaults.php');

function phorum_mod_gozerbot_after_post($message)
{
    global $PHORUM;
    $conf = $PHORUM['mod_gozerbot'];

    // Check whether we want to send a notification for the posted message.
    // This is a new thread starter.
    if ($message['parent_id'] == 0) {
        if (empty($PHORUM['mod_gozerbot']['do_new_threads'])) {
            return $message;
        }
    // This is a new reply message.
    } else {
        if (empty($PHORUM['mod_gozerbot']['do_new_replies'])) {
            return $message;
        }
    }

    // Format the forum path.
    $forums = phorum_db_get_forums($message['forum_id']);
    $forum = $forums[$message['forum_id']];
    if (!is_array($forum['forum_path'])) {
        $forum['forum_path'] = unserialize($forum['forum_path']);
    }
    $tmpforum = array_pop($forum['forum_path']);
    $path  = empty($PHORUM['mod_gozerbot']['full_path'])
           ? '' : implode(' / ', $forum['forum_path']) . ' / ';
    $forum = $tmpforum;

    // Format the read URL.
    $url = phorum_get_url(
        PHORUM_FOREIGN_READ_URL,
        $message['forum_id'], $message['thread'], $message['message_id']
    );
    // Strip auth data from the URL, if availble.
    if (isset($_POST[PHORUM_SESSION_LONG_TERM])) {
        $url = preg_replace(
            '!,?' . PHORUM_SESSION_LONG_TERM.'=' .
            urlencode($_POST[PHORUM_SESSION_LONG_TERM]).'!',
            '', $url
        );
    }

    // If the tinyurl service is enabled, then fetch a tiny URL.
    if (!empty($PHORUM['mod_gozerbot']['use_tinyurl']) &&
        function_exists('curl_init'))
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'url='.urlencode($url));
        curl_setopt($ch, CURLOPT_URL, 'http://tinyurl.com/api-create.php');
        $response = curl_exec($ch);
        if ($response && preg_match('!http://tinyurl\.com/\w+!', $response)) {
            $url = $response;
        }
        curl_close($ch);
    }

    // Format the message body.
    if (empty($conf['max_words'])) {
        $body = '';
    }
    else
    {
        require_once('./include/format_functions.php');
        $formatted = phorum_format_messages(array(
            $message['message_id'] => $message
        ));
        $body = $formatted[$message['message_id']]['body'];
        $body = str_replace("\n", "", trim($body));
        $body = preg_replace("/\s+/", " ", $body);
        $body = strip_tags($body);
        $body = html_entity_decode(
            $body, ENT_COMPAT, $PHORUM["DATA"]["HCHARSET"]
        );

        $words = explode(" ", $body, $conf['max_words'] + 1);
        if (count($words) > $conf['max_words']) {
            $body = implode(" ", array_slice($words, 0, $conf['max_words'])) .
                    ' ...';
        }
    }

    // Format the subject.
    $subject = $message['subject'];

    // Format the author.
    $author = $message['author'];

    // Format the threadsubject.
    if ($message['parent_id'] == 0) {
        $threadsubject = $subject;
    } else {
        $thread = phorum_db_get_message($message['thread'], 'message_id', TRUE);
        $threadsubject = $thread['subject'];
    }

    // Build the final message string.
    if ($message['message_id'] == $message['thread']) {
        $str = $PHORUM['DATA']['LANG']['MOD_GOZERBOT']['new_thread'];
    } else {
        $str = $PHORUM['DATA']['LANG']['MOD_GOZERBOT']['new_reply'];
    }
    $str = str_replace(
        array('%path%', '%forum%', '%subject%', '%threadsubject%',
              '%body%', '%user%', '%url%'),
        array($path, $forum, $subject, $threadsubject,
              $body, $author, $url),
        $str
    );

    // Send the  notification to the gozerbot.
    mod_gozerbot_send($str);

    return $message;
}

function mod_gozerbot_send($msg)
{
    global $PHORUM;
    static $bot = NULL;
    $conf = $PHORUM['mod_gozerbot'];

    // If PHP does not have socket support enabled, then silently ignore.
    if (!function_exists('socket_create')) { return; }

    // Don't run this if the module is not yet fully configured.
    // The other fields get default values from the defaults.php script.
    if (isset($conf['password']) && $conf['password'] !== '' &&
        isset($conf['target']) && $conf['target'] !== '') {

        // Setup the bot client.
        if ($bot === NULL)
        {
            require_once('./mods/gozerbot/class.GozerbotUDP.php');
            $bot = new GozerbotUDP();
            $bot->setHost($conf['host']);
            $bot->setPort($conf['port']);
            $bot->setPassword($conf['password']);
            $bot->setTarget($conf['target']);
            $bot->setCryptKey($conf['cryptkey']);
        }

        // Send the message to the bot.
        foreach (explode("\n", $msg) as $line) {
            $bot->send($line);
        }
    }
}

?>
