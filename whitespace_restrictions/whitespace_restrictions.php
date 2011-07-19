<?php

require_once "./mods/whitespace_restrictions/defaults.php";

function phorum_mod_whitespace_restrictions_format_fixup($messages)
{
    global $PHORUM;

    $limit = $PHORUM['mod_whitespace_restrictions']['limit'];

    foreach ($messages as $id => $message)
    {
        if (!isset($message['body'])) continue;

        $body = $message['body'];

        $newbody = array();
        $count = 0;
        foreach (explode('<phorum break>', $body) as $line)
        {
            if (preg_match('/^[\s\r\n]*$/', $line)) {
                $count ++;
            } else {
                $count = 0;
            }

            if ($count <= $limit) {
                $newbody[] = $line;
            }
        }

        $messages[$id]['body'] = implode('<phorum break>', $newbody);
    }

    return $messages;
}

?>
