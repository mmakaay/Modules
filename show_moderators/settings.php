<?php
if (!defined('PHORUM_ADMIN')) return;

require_once('./mods/show_moderators/defaults.php');

// save settings
if(count($_POST))
{
    $PHORUM['mod_show_moderators'] = array(
        'show_on_index'  => isset($_POST['show_on_index'])  ? 1 : 0,
        'show_on_list'   => isset($_POST['show_on_list'])   ? 1 : 0,
        'show_on_read'   => isset($_POST['show_on_read'])   ? 1 : 0,
        'include_admins' => isset($_POST['include_admins']) ? 1 : 0,
        'cache'          => (int) $_POST['cache'],
    );

    phorum_db_update_settings(array(
        'mod_show_moderators' => $PHORUM['mod_show_moderators']
    ));
    phorum_admin_okmsg('The settings were successfully saved');
}

include_once './include/admin/PhorumInputForm.php';
$frm = new PhorumInputForm ('', 'post', 'Save');
$frm->hidden('module', 'modsettings');
$frm->hidden('mod', 'show_moderators');

$frm->addbreak('Edit settings for the Show Moderators module');

$row = $frm->addrow('Automatically display moderators on the index page?', $frm->checkbox('show_on_index', '1', 'Yes', $PHORUM['mod_show_moderators']['show_on_index']));
$frm->addhelp($row, 'Automatic displaying on index page',
    "If you enable automatic displaying on the index page,
     then the moderators will be appended to the forum
     descriptions.<br/>
     <br/>
     If you disable this option, then you will have to
     edit the index_new.tpl and/or index.tpl to include
     the moderator info. You can make use of the following
     variables:<br/>
     <ul>
     <li>{FORUMS->MODERATORS}: a list of moderators over which you can run
          a {LOOP ...}. Inside the loop you can use:</li>
     <li>{FORUMS->MODERATORS->DISPLAY_NAME}: the name of the moderator user</li>
     <li>{FORUMS->MODERATORS->PROFILE}: the URL to the user's profile</li>
     </ul>"
);

$row = $frm->addrow('Automatically display moderators on the list pages?', $frm->checkbox('show_on_list', '1', 'Yes', $PHORUM['mod_show_moderators']['show_on_list']));
$frm->addhelp($row, 'Automatic displaying on list page',
    "If you enable automatic displaying on the list page,
     then the moderators will be displayed at the bottom
     of the page.<br/>
     <br/>
     If you disable this option, then you will have to
     edit the list.tpl and/or list_threads.tpl to include
     the moderator info. You can make use of the following
     variables:<br/>
     <ul>
     <li>{MODERATORS}: a list of moderators over which you can run
          a {LOOP ...}. Inside the loop you can use:</li>
     <li>{MODERATORS->DISPLAY_NAME}: the name of the moderator user</li>
     <li>{MODERATORS->PROFILE}: the URL to the user's profile</li>
     </ul>"
);

$row = $frm->addrow('Automatically display moderators on the read pages?', $frm->checkbox('show_on_read', '1', 'Yes', $PHORUM['mod_show_moderators']['show_on_read']));
$frm->addhelp($row, 'Automatic displaying on read page',
    "If you enable automatic displaying on the read page,
     then the moderators will be displayed at the bottom
     of the page.<br/>
     <br/>
     If you disable this option, then you will have to
     edit the read.tpl and/or read_threads.tpl to include
     the moderator info. You can make use of the following
     variables:<br/>
     <ul>
     <li>{MODERATORS}: a list of moderators over which you can run
          a {LOOP ...}. Inside the loop you can use:</li>
     <li>{MODERATORS->DISPLAY_NAME}: the name of the moderator user</li>
     <li>{MODERATORS->PROFILE}: the URL to the user's profile</li>
     </ul>"
);

$row = $frm->addrow('Include administrators in the moderator lists?', $frm->checkbox('include_admins', '1', 'Yes', $PHORUM['mod_show_moderators']['include_admins']));

$row = $frm->addrow('Cache moderator info? (0 = cache disabled)', $frm->text_box('cache', $PHORUM['mod_show_moderators']['cache'], 8) . ' seconds');

$frm->show();
?>
