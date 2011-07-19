<?php

// ----------------------------------------------------------------------
// This install file will be included by the module automatically
// at the first time that it is run. This file will take care of
// adding the custom user field "mod_hide_signatures" to Phorum.
// This way, the administrator won't have to create the custom field
// manually.
// ----------------------------------------------------------------------

if(!defined("PHORUM")) return;

require_once("./include/api/custom_profile_fields.php");

// See if we already have this field configured.
$existing = phorum_api_custom_profile_field_byname("mod_hide_signatures");

// We have, but it is a deleted field.
// In this case restore the field and its data.
if (!empty($existing["deleted"])) {
    phorum_api_custom_profile_field_restore($existing["id"]);
    $id = $existing["id"];
}
// Existing field.
elseif (!empty($existing)) {
    $id = $existing["id"];
}
// New field.
else {
    $id = NULL;
}

// Configure the field.
phorum_api_custom_profile_field_configure(array(
    'id'            => $id,
    'name'          => 'mod_hide_signatures',
    'length'        => 1, // we only need to store either "0" or "1"
    'html_disabled' => 0, // doesn't really matter what we use here
));

// Keep track of the module's install state.
phorum_db_update_settings(array("mod_hide_signatures_installed" => 1));

?>
