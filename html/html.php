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

if(!defined("PHORUM")) return;

// The path to the HTML Purifier stand alone distribution.
define('HTMLPURIFIER_PATH',dirname(__FILE__).'/htmlpurifier-4.0.0-standalone');

// Load HTMLPurifier.
ini_set(
    'include_path',
    ini_get('include_path') . PATH_SEPARATOR .
    HTMLPURIFIER_PATH.'/standalone'
);
require HTMLPURIFIER_PATH.'/HTMLPurifier.standalone.php';

// HTML Phorum Mod
function phorum_mod_html_format($data)
{
    global $PHORUM;
    static $purifier;
    static $config;

    // Setup the HTML Purifier object.
    if (!$purifier)
    {
        $cache = $PHORUM['cache'] . '/html_purifier';
        if (!file_exists($cache) && !mkdir($cache)) trigger_error(
            "The HTML module is unable to create the HTML Purifier " .
            "cache directory \"$cache\". Fix the cause of this problem " .
            "or disable the HTML module in the Phorum admin interface.",
            E_USER_ERROR
        );

        // Determine the doctype to use.
        $doctype = isset($PHORUM['mod_html']['doctype'])
                 ? $PHORUM['mod_html']['doctype']
                 : 'XHTML 1.0 Transitional';

        // Bootstrap the HTML Purifier.
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', $PHORUM['DATA']['CHARSET']);
        $config->set('HTML.Doctype', $doctype);
        $config->set('Cache.SerializerPath', $cache);
        $purifier = new HTMLPurifier($config);
    }

    $PHORUM = $GLOBALS["PHORUM"];

    foreach($data as $message_id => $message)
    {
        if(isset($message["body"]))
        {
            $body = $message["body"];

            // pull out the phorum breaks
            $body = str_replace("<phorum break>", "", $body);

            // Protect against poisoned null byte XSS attacks
            // (MSIE does not protect itself against these, so we have
            // to take care of that).
            str_replace("\0", "", $body);

            // restore tags where Phorum has killed them
            $body = preg_replace("!&lt;(\/*[a-z].*?)&gt;!si", "<$1>", $body);

            // restore escaped & and "
            $body = str_replace("&amp;", "&", $body);
            $body = str_replace("&quot;", '"', $body);

            // run the message through HTML Purifier for stripping out
            // possible XSS risks.
            $body = $purifier->purify($body);

            // put the phorum breaks back
            $body = str_replace("\n", "<phorum break>\n", $body);

            // strip any <phorum break> tags that got inside certain
            // blocks like tables (to prevent <table><br/><tr> like
            // code) and pre/xmp (newlines are shown, even without
            // <br/> tags).
            $block_tags="table|pre|xmp";

            preg_match_all("!(<($block_tags).*?>).+?(</($block_tags).*?>)!ims", $body, $matches);

            foreach($matches[0] as $block){
                $newblock=str_replace("<phorum break>", "", $block);
                $body=str_replace($block, $newblock, $body);
            }

            $data[$message_id]["body"] = $body;
        }
    }

    return $data;
}

?>
