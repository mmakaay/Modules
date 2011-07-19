<?php

// ----------------------------------------------------------------------
// This install file will be included by the module automatically 
// at the first time that it is run. This file will take care of 
// adding the custom user field "mod_hide_signatures" to Phorum.
// This way, the administrator won't have to create the custom field
// manually.
// ----------------------------------------------------------------------

if(!defined("PHORUM")) return;

// Initialize the settings array that we will be saving.
$settings = array( "mod_hide_signatures_installed" => 1 );

// Get the current custom profile fields.
$FIELDS = $PHORUM["PROFILE_FIELDS"];

// If this is not an array, we do not trust it.
if (! is_array($FIELDS)) {
    print "<b>Unexpected situation on installing " .
          "mod_hide_signatures:</b> \$PHORUM[\"PROFILE_FIELDS\"] " .
          "is not an array";
    return;
}

// Check if the field isn't already available.
$field_exists = false;
foreach ($FIELDS as $id => $fieldinfo) {
    if ($fieldinfo["name"] == "mod_hide_signatures") {
        $field_exists = true;
        break;
    }
}

// The field does not exist. Add it.
if (! $field_exists)
{
    $FIELDS["num_fields"]++;
    $FIELDS[$FIELDS["num_fields"]] = array(
        'name' => 'mod_hide_signatures',
        'length' => 1,
        'html_disabled' => 0,
    );

    $settings["PROFILE_FIELDS"] = $FIELDS;
}

// Save our settings.
if (!phorum_db_update_settings($settings)) {
    print "<b>Unexpected situation on installing " .
          "mod_hide_signatures</b>: Adding custom profile field " .
          "failed due to a database error";
}

?>
