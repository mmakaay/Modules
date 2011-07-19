<?php

if (!defined('PHORUM')) return;

function phorum_mod_attachment_uploader_before_textarea()
{
    global $PHORUM;
    include(phorum_get_template('attachment_uploader::before_textarea'));
}

function phorum_mod_attachment_uploader_start_output()
{
    if (phorum_page != 'post' ||
        empty($_POST['mod_attachment_uploader'])) return;

    global $PHORUM;
    include(phorum_get_template('attachment_uploader::update_after_upload'));
    exit;
}

?>
