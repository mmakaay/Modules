<?php

if (!defined("PHORUM")) return;

function phorum_mod_impersonate_user_generate_checksum($user_id, $salt = '')
{
    // Find the data for the target user.
    $user = phorum_api_user_get($user_id);
    if (empty($user)) trigger_error(
        "impersonate_user mod: Unable to find user with user_id $user_id",
        E_USER_ERROR
    );

    // Check if the current user is an administrator and has a long
    // term session active. We use the current user's password and
    // sessid_lt fields as part of the checksum salt.
    if (empty($GLOBALS["PHORUM"]["user"]["admin"]) ||
        empty($GLOBALS["PHORUM"]["user"]["sessid_lt"])) trigger_error(
        "impersonate_user mod: The current user is not an administrator or " .
        "the \"sessid_lt\" user field is empty.",
        E_USER_ERROR
    );

    // Generate signed checksum data.
    $checksum = phorum_generate_data_signature(
        $GLOBALS["PHORUM"]["user"]["sessid_lt"] .
        $GLOBALS["PHORUM"]["user"]["password"] .
        $user['user_id'] .
        $user['password'] .
        $user['date_added'] .
        $salt
    );

    return $checksum;
}

// This addon will be called for doing the actual switching between users.
function phorum_mod_impersonate_user_addon()
{
    $PHORUM = $GLOBALS["PHORUM"];

    if (empty($PHORUM['args']['action'])) trigger_error(
        'Illegal call to the impersonate_user mod addon handler: ' .
        'missing parameter "action".',
        E_USER_ERROR
    );

    if ($PHORUM['args']['action'] == 'impersonate')
    {
        // Only for administrators. We also check this in the
        // phorum_mod_impersonate_user_generate_checksum() function, but I
        // am kind of paranoid about the whole user switching business.
        if (empty($PHORUM["user"]["admin"])) trigger_error(
            'Illegal call to the impersonate_user mod addon handler: ' .
            'called by a user that is not an administrator.',
            E_USER_ERROR
        );

        // Retrieve target parameter.
        if (empty($PHORUM['args']['target'])) trigger_error(
            'Illegal call to the impersonate_user mod addon handler: ' .
            'missing parameter "target".',
            E_USER_ERROR
        );
        $target_id = (int) $PHORUM['args']['target'];

        // Retrieve the checksum parameter.
        if (empty($PHORUM['args']['checksum'])) trigger_error(
            'Illegal call to the impersonate_user mod addon handler: ' .
            'missing parameter "checksum".',
            E_USER_ERROR
        );
        $checksum = $PHORUM['args']['checksum'];

        // Check the checksum value.
        $compare = phorum_mod_impersonate_user_generate_checksum($target_id);
        if ($compare !== $checksum) trigger_error(
            'Illegal call to the impersonate_user mod addon handler: ' .
            'the checksum for impersonating user id ' . $target_id .
            ' is invalid.',
            E_USER_ERROR
        );

        // Setup the data for enabling switching back to the original user.
        // This uses some salt to add a bit of randomness to the switchback
        // cookie info.
        $salt = md5(microtime());
        $admin_id  = $PHORUM['user']['user_id'];
        $checksum = phorum_mod_impersonate_user_generate_checksum($admin_id, $salt);
        phorum_api_user_save_settings(array(
            'mod_impersonate_user' => $target_id . ":" . $checksum
        ));

        // A cookie is used to remember the switching back info for the user.
        setcookie(
            "mod_impersonate_user",
            "$admin_id:$target_id:$checksum",
            0, $PHORUM["session_path"], $PHORUM["session_domain"]
        );

        // Impersonate the target user.
        phorum_api_user_set_active_user(
            PHORUM_FORUM_SESSION,
            $target_id,
            PHORUM_FLAG_SESSION_ST
        );
        phorum_api_user_session_create(
            PHORUM_FORUM_SESSION,
            PHORUM_SESSID_RESET_LOGIN
        );
    }
    elseif ($PHORUM['args']['action'] == 'switchback')
    {
        // Check if we have a switchback cookie.
        if (!isset($_COOKIE['mod_impersonate_user']) ||
            strstr($_COOKIE['mod_impersonate_user'], ':') === FALSE) {
            trigger_error(
                'Illegal call to the impersonate_user mod addon handler: ' .
                'the mod_impersonate_user cookie containing the switchback ' .
                'checksum is not available.',
                E_USER_ERROR
            );
        }

        // Retrieve the data from the cookie.
        list($admin_id, $c_target_id, $c_checksum) =
            explode(':', $_COOKIE['mod_impersonate_user']);
        settype($admin_id, "int");
        settype($c_target_id, "int");

        // Check if the target id in the cookie matches the active user.
        if ($c_target_id != $PHORUM['user']['user_id']) trigger_error(
            'Illegal call to the impersonate_user mod addon handler: ' .
            'the switchback cookie is not owned by the active user',
            E_USER_ERROR
        );

        // Retrieve switchback data that we stored in the admin user profile.
        $adminuser = phorum_api_user_get((int)$admin_id);
        if (empty($adminuser)) trigger_error(
            'Illegal call to the impersonate_user mod addon handler: ' .
            'no user found for switchback user_id $admin_id.',
            E_USER_ERROR
        );
        $p_target_id = NULL;
        $p_checksum  = NULL;
        if (!empty($adminuser['settings_data']['mod_impersonate_user'])) {
            list($p_target_id, $p_checksum) =
                split(':', $adminuser['settings_data']['mod_impersonate_user']);
        }
        if ($p_target_id !== NULL) settype($p_target_id, "int");

        // Check if switchback user data was found in the profile.
        if ($p_target_id === NULL || $p_checksum === NULL) trigger_error(
            'Illegal call to the impersonate_user mod addon handler: ' .
            'no switchback user data found in the settings_data for ' .
            "admin user $admin_id.",
            E_USER_ERROR
        );

        // Check if the checksums and user ids match.
        if ($p_target_id !== $c_target_id ||
            $p_checksum !== $c_checksum) trigger_error(
            'Illegal call to the impersonate_user mod addon handler: ' .
            'cookie checksum and/or user_ids for impersonating user id ' .
            "$c_target_id do not match the data that was stored in the " .
            'admin user profile.',
            E_USER_ERROR
        );

        // Clean up the switchback cookie.
        setcookie(
            "mod_impersonate_user", "",
            0, $PHORUM["session_path"], $PHORUM["session_domain"]
        );

        // Switch back to the admin user.
        phorum_api_user_set_active_user(
            PHORUM_FORUM_SESSION,
            $admin_id,
            PHORUM_FLAG_SESSION_ST
        );
        phorum_api_user_session_create(
            PHORUM_FORUM_SESSION,
            PHORUM_SESSID_RESET_LOGIN
        );
    }
    else trigger_error(
        'Illegal call to the impersonate_user mod addon handler: ' .
        'the value for parameter "action" is invalid.',
        E_USER_ERROR
    );


    phorum_redirect_by_url(phorum_get_url(PHORUM_INDEX_URL));
}

// Add a impersonate user link to the user profile.
function phorum_mod_impersonate_user_profile($profile)
{
    // Only for administrators, we create the impersonate user link.
    if (empty($GLOBALS["PHORUM"]["user"]["admin"])) return $profile;

    // Add the impersonate user link to the profile data.
    $checksum = phorum_mod_impersonate_user_generate_checksum($profile['user_id']);
    $profile['URL']['IMPERSONATE_USER'] = phorum_get_url(
        PHORUM_ADDON_URL,
        "module=impersonate_user",
        "action=impersonate",
        "target=".(int)$profile['user_id'],
        "checksum=".urlencode($checksum)
    );

    return $profile;
}

// Setup data for the impersonate user notice and switch back link.
function phorum_mod_impersonate_user_common()
{
    global $PHORUM;
    $lang = $PHORUM['DATA']['LANG']['mod_impersonate_user'];

    // Check if we have a switchback cookie.
    if (!isset($_COOKIE['mod_impersonate_user']) ||
        strstr($_COOKIE['mod_impersonate_user'], ':') === FALSE) return;

    list($admin_id, $target_id, $checksum) =
        explode(':', $_COOKIE['mod_impersonate_user']);

    // If we are already active using the $admin_id or if the $target_id
    // is not the active user's user_id, then we do not have to display
    // the notice. This might happen if cleaning up the switchback cookie
    // failed for some reason or if logging out / loggin into a different
    // account.
    if ($admin_id == $PHORUM['user']['user_id'] ||
        $target_id != $PHORUM['user']['user_id']) {

        // Clean up the switchback cookie.
        setcookie(
            "mod_impersonate_user", "",
            0, $PHORUM["session_path"], $PHORUM["session_domain"]
        );

        return;
    }

    // Setup template data.

    $PHORUM['DATA']['MOD_IMPERSONATE_USER']['TARGET'] =
        phorum_api_user_get_display_name($PHORUM['user']['user_id']);

    $PHORUM['DATA']['MOD_IMPERSONATE_USER']['ADMIN'] =
        phorum_api_user_get_display_name($admin_id);

    $PHORUM['DATA']['MOD_IMPERSONATE_USER']['NOTICE'] =
        str_replace(
            '%user%',
            $PHORUM['DATA']['MOD_IMPERSONATE_USER']['TARGET'],
            $lang['Notice']
        );

    $PHORUM['DATA']['MOD_IMPERSONATE_USER']['SWITCHBACK'] =
        str_replace(
            '%user%',
            $PHORUM['DATA']['MOD_IMPERSONATE_USER']['ADMIN'],
            $lang['SwitchBack']
        );

    $PHORUM['DATA']['MOD_IMPERSONATE_USER']['URL']['SWITCHBACK'] =
        phorum_get_url(
            PHORUM_ADDON_URL,
            "module=impersonate_user",
            "action=switchback"
        );

    $PHORUM['DATA']['MOD_IMPERSONATE_USER']['URL']['TEMPLATES'] =
        $PHORUM['http_path'] . '/mods/impersonate_user/templates';

    $PHORUM['DATA']['MOD_IMPERSONATE_USER']['CLEAR_COOKIE'] =
        'mod_impersonate_user=cleared;' .
        (!empty($PHORUM['session_path'])
         ? 'path=' . $PHORUM['session_path'] . ';'
         : '') .
        (!empty($PHORUM['session_domain'])
         ? 'domain=' . $PHORUM['session_domain'] . ';'
         : '') .
        'expires=Thu, 01-Jan-1970 00:00:01 GMT';
}

function phorum_mod_impersonate_user_after_header()
{
    global $PHORUM;

    // Display a switch back notice if data was setup in the common hook.
    if (empty($PHORUM['DATA']['MOD_IMPERSONATE_USER']['URL']['SWITCHBACK'])) {
        return;
    }
    include(phorum_get_template('impersonate_user::notice'));
}

// Display the impersonate user link on the profile page.
function phorum_mod_impersonate_user_before_footer()
{
    $PHORUM = $GLOBALS["PHORUM"];

    if (!empty($PHORUM['DATA']['PROFILE']['URL']['IMPERSONATE_USER']) &&
        phorum_page == 'profile') {
        include(phorum_get_template('impersonate_user::profile'));
    }
}

function phorum_mod_impersonate_user_cc_panel($data)
{
    global $PHORUM;

    // Impersonate user is only available for admins.
    if (empty($PHORUM['user']['admin'])) return $data;

    $PHORUM['DATA']['MOD_IMPERSONATE_USER']['URL']['TEMPLATES'] =
        $PHORUM['http_path'] . '/mods/impersonate_user/templates';

    // We set {MODERATOR} here, so the cc_index.tpl will show the
    // moderate header. This should already be the case for administrators,
    // but we make it extra sure here.
    if (!empty($PHORUM['user']['admin'])) {
        $PHORUM['DATA']['MODERATOR'] = TRUE;
    }

    if ($data['panel'] == 'impersonate_user') {
        // Separate include file, because of its length.
        include('./mods/impersonate_user/cc_panel.php');

        $data['handled'] = TRUE;
    }

    return $data;
}

function phorum_mod_impersonate_user_cc_moderator_menu()
{
    global $PHORUM;

    // Only for administrators, we create the impersonate user link.
    if (empty($PHORUM["user"]["admin"])) return $profile;

    // Generate the require template data for the control panel menu button.
    if ($PHORUM["DATA"]["PROFILE"]["PANEL"] == 'impersonate_user')
        $PHORUM["DATA"]["IMPERSONATE_USER_PANEL_ACTIVE"] = TRUE;
    $PHORUM["DATA"]["URL"]["CC_IMPERSONATE_USER"] =
        phorum_get_url(PHORUM_CONTROLCENTER_URL, "panel=impersonate_user");

    // Show the menu button.
    include(phorum_get_template('impersonate_user::cc_menu_item'));
}

?>
