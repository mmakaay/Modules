<?php

define("SIGNATURE_MARKER_PRE",  "###format_signatures_pre###");
define("SIGNATURE_MARKER_POST", "###format_signatures_post###");

// Defaults to use. If these are changed, remember to change them
// in settings.php as well.
if (!isset($GLOBALS['PHORUM']['mod_format_signatures']['prefix']) &&
    !isset($GLOBALS['PHORUM']['mod_format_signatures']['postfix'])) {
    $GLOBALS['PHORUM']['mod_format_signatures']['prefix'] =
      "<br/>\n<hr/>\n";
    $GLOBALS['PHORUM']['mod_format_signatures']['postfix'] = "";
}

function phorum_mod_format_signatures_read_user_info($users)
{
    // Do not handle this on the control center page. That is where
    // the user can edit his signature. If we would add the signature
    // markers here, they would show up verbatim in the signature editor.
    if (phorum_page === 'control') return $users;

    foreach ($users as $id => $user)
    {
        $sig = trim($user['signature']);
        if ($sig != '') {
            $users[$id]['signature'] =
                SIGNATURE_MARKER_PRE . $sig . SIGNATURE_MARKER_POST;
        }
    }

    return $users;
}

function phorum_mod_format_signatures_format($messages)
{
    $conf = $GLOBALS['PHORUM']['mod_format_signatures'];

    foreach ($messages as $id => $message) {
        if (! isset($message['body'])) continue;
        $messages[$id]['body'] = str_replace(
            array(SIGNATURE_MARKER_PRE, SIGNATURE_MARKER_POST),
            array($conf['prefix'], $conf['postfix']),
            $message['body']
        );
    }

    return $messages;
}

?>
