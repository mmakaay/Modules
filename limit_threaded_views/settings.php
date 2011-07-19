<?php

if (!defined("PHORUM_ADMIN")) return;

require_once('./mods/limit_threaded_views/defaults.php');

if (count($_POST))
{
    $PHORUM['mod_limit_threaded_views'] = array(
        'read_threaded' => (int) $_POST['read_threaded'],
        'read_hybrid'   => (int) $_POST['read_hybrid'],
        'list_threaded' => (int) $_POST['list_threaded'],
        'show_notice'   => isset($_POST['show_notice']) ? 1 : 0
    );

    phorum_db_update_settings(array(
        'mod_limit_threaded_views' => $PHORUM['mod_limit_threaded_views']
    ));

    phorum_admin_okmsg('The module settings were successfully saved');
}

include_once './include/admin/PhorumInputForm.php';
$frm = new PhorumInputForm ('', 'post', 'Submit');
$frm->hidden('module', 'modsettings');
$frm->hidden('mod', 'limit_threaded_views');

$frm->addbreak('Edit settings for the Limit Threaded Views module');

$frm->addrow("Maximum number of messages for threaded list", $frm->text_box("list_threaded", $PHORUM['mod_limit_threaded_views']['list_threaded'], 10));

$frm->addrow("Maximum number of messages for threaded read", $frm->text_box("read_threaded", $PHORUM['mod_limit_threaded_views']['read_threaded'], 10));

$frm->addrow("Maximum number of messages for hybrid read", $frm->text_box("read_hybrid", $PHORUM['mod_limit_threaded_views']['read_hybrid'], 10));

$frm->addrow("Show a notice to the user if the view is changed?", $frm->checkbox("show_notice", "1", "Yes", $PHORUM["mod_limit_threaded_views"]["show_notice"]));

$frm->show();

?>
