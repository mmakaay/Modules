<?php
// A simple helper script that will setup initial module
// settings in case one of these settings is missing.

if(!defined("PHORUM") && !defined("PHORUM_ADMIN")) return;

if (! isset($GLOBALS["PHORUM"]["mod_banner_manager"])) {
    $GLOBALS["PHORUM"]["mod_banner_manager"] = array();
}

// By default, we will automatically display the banner.
if (! isset($GLOBALS["PHORUM"]["mod_banner_manager"]["hide_banner"])) {
    $GLOBALS["PHORUM"]["mod_banner_manager"]["hide_banner"] = 0;
}

// The default banner position, in case of automatic displaying.
if (! isset($GLOBALS["PHORUM"]["mod_banner_manager"]["banner_position"])) {
    $GLOBALS["PHORUM"]["mod_banner_manager"]["banner_position"] = "after_header";
}

// The default banner alignment, in case of automatic displaying.
if (! isset($GLOBALS["PHORUM"]["mod_banner_manager"]["banner_alignment"])) {
    $GLOBALS["PHORUM"]["mod_banner_manager"]["banner_alignment"] = "center";
}

// The default list of banners.
if (! isset($GLOBALS["PHORUM"]["mod_banner_manager"]["banners"])) {
    $GLOBALS["PHORUM"]["mod_banner_manager"]["banners"] = array();
}

// The default list of links.
if (! isset($GLOBALS["PHORUM"]["mod_banner_manager"]["links"])) {
    $GLOBALS["PHORUM"]["mod_banner_manager"]["links"] = array();
}

?>
