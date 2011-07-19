<?php
// A simple helper script that will setup initial module
// settings in case one of these settings is missing.

if(!defined("PHORUM") && !defined("PHORUM_ADMIN")) return;

if (! isset($GLOBALS["PHORUM"]["mod_markallforumsread"])) {
    $GLOBALS["PHORUM"]["mod_markallforumsread"] = array();
}

// By default, we will only display the link at the bottom of the index page.
if (! isset($GLOBALS["PHORUM"]["mod_markallforumsread"]["show_after_header"]))
    $GLOBALS["PHORUM"]["mod_markallforumsread"]["show_after_header"] = 0;
if (! isset($GLOBALS["PHORUM"]["mod_markallforumsread"]["show_before_footer"]))
    $GLOBALS["PHORUM"]["mod_markallforumsread"]["show_before_footer"] = 1;

// By default, always show the mark read link.
if (! isset($GLOBALS["PHORUM"]["mod_markallforumsread"]["show_only_if_new"]))
    $GLOBALS["PHORUM"]["mod_markallforumsread"]["show_only_if_new"] = 0;
?>
