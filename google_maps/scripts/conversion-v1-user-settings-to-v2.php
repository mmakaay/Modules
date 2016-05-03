#!/usr/bin/php
<?php

// Conversion of module v1 user settings data to v2 user settings data

// if we are running in the webserver, bail out
if ('cli' != php_sapi_name()) {
    echo 'This script cannot be run from a browser.';
    return;
}

define('PHORUM_ADMIN', 1);
define('phorum_page', 'conversion_google_maps_user_data');

require_once '../api.php';

chdir(dirname(__FILE__).'/../../..');
require_once './common.php';
require_once './include/api/user.php';
require_once './include/api/custom_profile_fields.php';

// Make sure that the output is not buffered.
phorum_ob_clean();

if (! ini_get('safe_mode')) {
    set_time_limit(0);
    ini_set('memory_limit', '64M');
}

print "\nConversion of module v1 user settings data to v2 user settings data ...\n";

// Collect all users which have a "mod_google_maps" custom user profile field.
$field = phorum_api_custom_profile_field_byname('mod_google_maps');
if (empty($field)) trigger_error(
    'No custom profile field named "mod_google_maps" available',
    E_USER_ERROR
);
$user_ids = phorum_api_user_search_custom_profile_field(
    $field['id'], '%', '*', TRUE
);
$count_total = count($user_ids);
$size = strlen($count_total);

// Retrieve the data for the users that were found.
$users = phorum_api_user_get($user_ids);

$count = 0;
foreach ($users as $user) {
    $mapstate = $user['mod_google_maps'];

    // Upgrade the user data if it looks like version 1 data.
    if (isset($mapstate['marker'])) {
        $mapstate = mod_google_maps_upgrade_userdata($mapstate);
        phorum_api_user_save
            ( array
                  ( 'user_id'         => $user['user_id'],
                    'mod_google_maps' => $mapstate ) );
    }

    $count ++;

    $perc = floor(($count/$count_total)*100);
    $barlen = floor(20*($perc/100));
    $bar = '[';
    $bar .= str_repeat('=', $barlen);
    $bar .= str_repeat(' ', (20-$barlen));
    $bar .= ']';
    printf
        ( "updating %{$size}d / %{$size}d  %s (%d%%)\r",
          $count, $count_total, $bar, $perc );
}
print "\n\n";
?>
