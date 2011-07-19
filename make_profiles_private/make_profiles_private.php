<?php

if(!defined("PHORUM")) return;

// This module has no settings. It makes ALL profiles private.
// Users do not have the option of making their profile public.
function phorum_mod_make_profiles_private_page_profile ($data)
{
    global $PHORUM;

    // We should have the requested user_id in the arguments.
    if (empty($PHORUM['args'][1])) {
        phorum_redirect_by_url(phorum_get_url(PHORUM_INDEX_URL));
        exit();
    }
    $user_id = (int)$PHORUM["args"][1];

    // Is user fully logged in? If not, kick them to login page.
    // If the user is not fully logged in, redirect to page
    // telling them to log in.
    if (!$PHORUM["DATA"]["FULLY_LOGGEDIN"]) {
        phorum_redirect_by_url(phorum_get_url(
            PHORUM_ADDON_URL,
            'module=make_profiles_private',
            'user_id='.$user_id
        ));
        exit();
    }
}

function phorum_mod_make_profiles_private_display ()
{
    global $PHORUM;

    // We should have the requested user_id in the arguments.
    if (empty($PHORUM['args']['user_id'])) {
        phorum_redirect_by_url(phorum_get_url(PHORUM_INDEX_URL));
        exit();
    }
    $user_id = (int)$PHORUM['args']['user_id'];

    phorum_build_common_urls();

    // The user must be redirected back to the profile page after
    // logging in. Therefore we provide the redir parameter. Without it,
    // after logging in, the user would be redirected to this notice page.
    $PHORUM["DATA"]["URL"]["LOGINOUT"] = phorum_get_url(
        PHORUM_LOGIN_URL,
        'redir='.rawurlencode(phorum_get_url(PHORUM_PROFILE_URL, $user_id))
    );

    phorum_output('make_profiles_private::notice');
}

?>
