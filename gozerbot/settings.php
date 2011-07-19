<?php
if (!function_exists('socket_create')) {
    phorum_admin_error(
        'Your PHP installation lacks sockets support.<br/>' .
        'Due to this problem, this module will not be able ' .
        'to send notifications.'
    );
}
?>

<div style="
    padding: 5px 15px;
    margin-right: 2px;
    margin-bottom: 5px;
    background-color: black;
    color: #00ff00;
    float: right;
    font-size: 30px">
  Phorum Gozerbot ;]
</div>
Gozerbot lives at <a href="http://www.gozerbot.org/">http://www.gozerbot.org</a><br/>
This module was written and tested against version 0.8
<br style="clear:both"/>

<?php

if (!defined("PHORUM_ADMIN")) return;

// Include for first time initialization.
include('./mods/gozerbot/defaults.php');

// Send a test message to Gozerbot.
if (isset($_POST['test_message'])) {
    require_once('./mods/gozerbot/gozerbot.php'); 
    mod_gozerbot_send($_POST['test_message']);
    phorum_admin_okmsg('A test message was sent to Gozerbot');
}
// Configure the Gozerbot module.
elseif (count($_POST))
{
    $PHORUM['mod_gozerbot'] = array
    (
        'host'                   => trim($_POST['host']),
        'port'                   => (int)$_POST['port'],
        'password'               => trim($_POST['password']),
        'cryptkey'               => trim($_POST['cryptkey']),
        'target'                 => trim($_POST['target']),
        'max_words'              => (int)$_POST['max_words'],
        'full_path'              => empty($_POST['full_path']) ? 0 : 1,
        'use_tinyurl'            => empty($_POST['use_tinyurl']) ? 0 : 1,
        'do_new_threads'         => empty($_POST['do_new_threads']) ? 0 : 1,
        'do_new_replies'         => empty($_POST['do_new_replies']) ? 0 : 1
    );

    // Include for applying defaults to emptied form fields.
    include('./mods/gozerbot/defaults.php');

    if (strlen($PHORUM['mod_gozerbot']['password']) == 0) {
        phorum_admin_error(
            'Please, enter the password for the Gozerbot connection. ' .
            'This one should be the same as the option "udppassword" ' .
            'from the Gozerbot configuration file.'
        );
    }
    elseif (strlen($PHORUM['mod_gozerbot']['cryptkey']) != 0 &&
        strlen($PHORUM['mod_gozerbot']['cryptkey']) != 16) {
        phorum_admin_error(
            'The encryption string should either be empty or ' .
            'exactly 16 characters long.'
        );
    }
    elseif (strlen($PHORUM['mod_gozerbot']['target']) == 0) {
        phorum_admin_error(
            'Please, enter the target #channel or nickname to send ' .
            'the messages to.'
        );
    }
    elseif (!preg_match(
        // I based this on RFC 1459, 2.3.1. Message format in 'pseudo' BNF
        '/^(?:[#&][^\s\b\0,]+|[a-zA-Z][\w\[\]\\\`\^\{\}-]*)$/',
        $PHORUM['mod_gozerbot']['target']
    )) {
        phorum_admin_error(
            'The #channel or nickname to use as the target is invalid.'
        );
    }
    else
    {
        phorum_db_update_settings(array(
            "mod_gozerbot" => $PHORUM["mod_gozerbot"]
        ));
        phorum_admin_okmsg("Settings updated");
    }
}

// ----------------------------------------------------------------------
// Form: Configure the Gozerbot connection and notifications
// ----------------------------------------------------------------------

require_once("./include/admin/PhorumInputForm.php");
$frm = new PhorumInputForm ("", "post", "Save");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "gozerbot");

$frm->addbreak("Configure the Gozerbot module");

$row = $frm->addsubbreak('Configure the Gozerbot connection');
$frm->addhelp($row, 'Configure the Gozerbot connection',
    "Below, you can configure the connection to the Gozerbot UDP server.
     Make sure that the UDP server functionality is enabled in Gozerbot's
     configuration file (udp = 1) and that the IP-address from which
     Phorum will connect to the server is in the IP access list (udpallow).
     For each option, the corresponding Gozerbot configuration option is
     added in parentheses."
);

$frm->addrow("Hostname (udphost)", $frm->text_box("host", $PHORUM['mod_gozerbot']['host'], 40));
$frm->addrow("Port number (udpport)", $frm->text_box("port", $PHORUM['mod_gozerbot']['port'], 5));
$frm->addrow("The connection password (udppassword)", $frm->text_box("password", $PHORUM['mod_gozerbot']['password']));
$frm->addrow("The encryption key (udpseed)<div style=\"font-size:80%\">Empty = no encryption, 16 characters = using encryption</div>", $frm->text_box("cryptkey", $PHORUM['mod_gozerbot']['cryptkey'], 17, 16));
$row = $frm->addrow("The target (must be in the \"udpallow\" list)", $frm->text_box("target", $PHORUM['mod_gozerbot']['target']));
$frm->addhelp($row, "The target",
    "This target specifies to what #channel or nickname the messages must
     be sent. To send data to channel foobar, specify \"#foobar\" as the
     target. To send data to user johndoe, specify \"johndoe\" as the
     target.<br/><br/>
     Note that the udpallow list in Gozerbot must be configured to
     accept your target of choice."
);
$frm->addrow("The maximum allowed number of words in a notification", $frm->text_box("max_words", $PHORUM['mod_gozerbot']['max_words'], 5));

$row = $frm->addrow("Show the full folder path for a forum in notifications?", $frm->checkbox('full_paths', 1, "Yes", $PHORUM['mod_gozerbot']['full_path']));
$frm->addhelp($row, "Show the full folder path", "If this option is enabled, then the full forum path will be shown in notifications (E.g. \"My folder / My subfolder / My forum\"). If you don't use folders extensively or if the names of the forums are clear enough on their own, then you can disable this option to only show the forum names (E.g. \"My forum\").");

$msg = function_exists('curl_init') ? '' : '<div style="color:red">This feature requires "curl" support for PHP, however curl does not<br/>seem to be enabled on your server. Therefore this feature will not work.<br/>Contact your provider if you still want to use tinyurl.com.</div>';
$row = $frm->addrow("Use the tinyurl.com service to shorten URLs?$msg", $frm->checkbox('use_tinyurl', 1, "Yes", $PHORUM['mod_gozerbot']['use_tinyurl']));

$frm->addsubbreak('Configure what notifications have to be sent to Gozerbot');
$frm->addrow($frm->checkbox('do_new_threads', 1, "New threads that are posted", $PHORUM['mod_gozerbot']['do_new_threads']));
$frm->addrow($frm->checkbox('do_new_replies', 1, "New reply messages that are posted", $PHORUM['mod_gozerbot']['do_new_replies']));

$frm->show();

// ----------------------------------------------------------------------
// Form: Test sending a message to Gozerbot
// ----------------------------------------------------------------------

if (!function_exists('socket_create')) return;

print "<br/><br/>";
require_once("./include/admin/PhorumInputForm.php");
$frm = new PhorumInputForm ("", "post", "Send test message");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "gozerbot");

$frm->addbreak("Test the configured Gozerbot connection");

$frm->addrow(
    "Enter the message to send",
    $frm->text_box('test_message', $_POST['test_message'])
);

$frm->show();

?>
