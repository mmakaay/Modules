<?php

function phorum_mod_breadcrumbs_home_to_external_site()
{
    global $PHORUM;

    // The URL to use for the new 'Home' link.
    $external_url =
        empty($PHORUM['mod_breadcrumbs_home_to_external_site']['url'])
        ? '/'
        : $PHORUM['mod_breadcrumbs_home_to_external_site']['url'];

    $PHORUM['DATA']['BREADCRUMBS'][0]['TEXT'] =
        $PHORUM['DATA']['LANG']['Forums'];
    $PHORUM['DATA']['BREADCRUMBS'][0]['TYPE'] = 'folder';

    array_unshift(
        $PHORUM['DATA']['BREADCRUMBS'],
        array(
            'URL'  => $external_url,
            'TEXT' => $PHORUM['DATA']['LANG']['Home'],
            'TYPE' => 'root'
        )
    );
}

?>
