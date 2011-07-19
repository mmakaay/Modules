<?php
///////////////////////////////////////////////////////////////////////////////
//                                                                           //
// Copyright (C) 2009  Phorum Development Team                               //
// http://www.phorum.org                                                     //
//                                                                           //
// This program is free software. You can redistribute it and/or modify      //
// it under the terms of either the current Phorum License (viewable at      //
// phorum.org) or the Phorum License that was distributed with this file     //
//                                                                           //
// This program is distributed in the hope that it will be useful,           //
// but WITHOUT ANY WARRANTY, without even the implied warranty of            //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                      //
//                                                                           //
// You should have received a copy of the Phorum License                     //
// along with this program.                                                  //
//                                                                           //
///////////////////////////////////////////////////////////////////////////////

if (!defined("PHORUM_ADMIN")) return;

// Available doctypes as supported by HTML Purifier
$doctypes = array(
    'HTML 4.01 Strict'         => 'HTML 4.01 Strict',
    'HTML 4.01 Transitional'   => 'HTML 4.01 Transitional',
    'XHTML 1.0 Strict'         => 'HTML 1.0 Strict',
    'XHTML 1.0 Transitional'   => 'XHTML 1.0 Transitional (Phorum\'s default)',
    'XHTML 1.1'                => 'XHTML 1.1'
);

// Initialize the doctype configuration.
if (!is_array($PHORUM['mod_html'])) $PHORUM['mod_html'] = array();
if (!isset($PHORUM['mod_html']['doctype'])) {
    // Phorum's template default
    $PHORUM['mod_html']['doctype'] = 'XHTML 1.0 Transitional';
}

// Save settings.
if (count($_POST))
{
    if (isset($_POST['doctype']) && isset($doctypes[$_POST['doctype']])) {
        $PHORUM['mod_html']['doctype'] = $_POST['doctype'];
    }

    phorum_db_update_settings(array('mod_html' => $PHORUM['mod_html']));
    phorum_admin_okmsg("The settings were successfully saved.");
}

require_once('./include/admin/PhorumInputForm.php');
$frm = new PhorumInputForm ("", "post", "Save settings");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "html");

$frm->addbreak("Configuration for the BBcode module");

$frm->addmessage(
    "<p>
       To be able to correctly clean up HTML code in a message,
       this module needs to know what kind of HTML code (the \"doctype\")
       to expect. If you are unsure what doctype you are using,
       then take a look at your header.tpl template file. At the start
       of that file, you will find a line looking somewhat like this:
     </p>
     <p>
       &lt;!DOCTYPE html PUBLIC \"-//W3C//DTD <b style=\"color:green\">XHTML 1.0 Transitional</b>//EN\"
       \"http://www.w3.org/TR/xhtml1/DTD/<b style=\"color:green\">xhtml1-transitional</b>.dtd\"&gt;
     </p>
     <p>
       In this example, you can see that the HTML doctype to use would be
       \"XHTML 1.0 Transitional\". This is in fact the default doctype that
       Phorum uses for its bundled templates. So if you are using
       (a modified version of) one of Phorum's bundled templates,
       then you can select this doctype.
     </p>"
);

$frm->addrow(
    'What document type do you use for your template?',
    $frm->select_tag('doctype', $doctypes, $PHORUM['mod_html']['doctype'])
);

$frm->show();
?>
