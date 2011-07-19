<?php

if (!defined('PHORUM')) return;

function phorum_mod_pmreply_unquoted_read($messages)
{
    foreach ($messages as $id => $message)
    {
        if (empty($message['URL']['PM'])) continue;

        $messages[$id]['URL']['PM'] = phorum_get_url(
            PHORUM_PM_URL,
            "page=send",
            "to_id=".(int)$message['user_id']
        );
    }

    return $messages;
}

?>
