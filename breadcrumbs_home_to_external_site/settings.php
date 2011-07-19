<?php
if (!defined('PHORUM_ADMIN')) return;

// save settings
if (count($_POST))
{
    $PHORUM['mod_breadcrumbs_home_to_external_site'] = array(
        'url' => trim($_POST['url'])
    );

    phorum_db_update_settings(array(
        'mod_breadcrumbs_home_to_external_site' =>
        $PHORUM['mod_breadcrumbs_home_to_external_site']
    ));

    phorum_admin_okmsg('The settings were successfully saved');
}

if (empty($PHORUM['mod_breadcrumbs_home_to_external_site']['url'])) {
    $PHORUM['mod_breadcrumbs_home_to_external_site']['url'] = '/';
}

include_once './include/admin/PhorumInputForm.php';
$frm = new PhorumInputForm ('', 'post', 'Save');
$frm->hidden('module', 'modsettings');
$frm->hidden('mod', 'breadcrumbs_home_to_external_site');

$frm->addbreak(
    'Edit settings for the Breadcrumbs \'Home\' to External Site module'
);

$row = $frm->addrow(
    "What URL to use for the 'Home' breadcrumb?",
    $frm->text_box(
        'url',
        $PHORUM['mod_breadcrumbs_home_to_external_site']['url'], 50
    )
);

$frm->addhelp($row, 
    "What URL to use for the 'Home' breadcrumb?",
    "You can make use of full URLs (e.g. 'http://www.phorum.org'),
     absolute paths (e.g. '/main') and relative paths (e.g. '../')
     to specify the URL for the 'Home' breadcrumb."
);

$frm->show();
?>
