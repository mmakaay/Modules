<?php

if (!defined('PHORUM_CONTROL_CENTER')) return;

global $PHORUM;
$lang = $PHORUM["DATA"]["LANG"]["mod_impersonate_user"];

// The maximum number of search results we want to show,
// before complaining that it's becoming too much.
define('MAX_SEARCH_RESULTS', 50);

// ----------------------------------------------------------------------
// Find a list of users to show
// ----------------------------------------------------------------------

$users = array();
            
// Initialize search template data.
$PHORUM['DATA']['SEARCH_USER_ID'] = '';
$PHORUM['DATA']['SEARCH_USERNAME'] = '';
$PHORUM['DATA']['SEARCH_DISPLAY_NAME'] = '';
$PHORUM['DATA']['SEARCH_EMAIL'] = '';

if (isset($_POST['do_search']))
{
    // Build the fields to search on.
    $search_fields = array('active');
    $search_values = array(PHORUM_USER_ACTIVE);
    $search_operators = array('=');
    if (isset($_REQUEST['search_user_id'])) {
        $search = (int) trim($_REQUEST['search_user_id']);
        if ($search != '') {
            $search_fields[] = 'user_id';
            $search_values[] = $search;
            $search_operators[] = '=';
            $PHORUM['DATA']['SEARCH_USER_ID'] = $search;
        }
    }
    if (isset($_REQUEST['search_username'])) {
        $search = trim($_REQUEST['search_username']);
        if ($search != '') {
            $search_fields[] = 'username';
            $search_values[] = $search;
            $search_operators[] = '*';
        }
        $PHORUM['DATA']['SEARCH_USERNAME'] =
            htmlspecialchars($search, ENT_COMPAT, $PHORUM["DATA"]["HCHARSET"]);
    }
    if (isset($_REQUEST['search_display_name'])) {
        $search = trim($_REQUEST['search_display_name']);
        if ($search != '') {
            $search_fields[] = 'display_name';
            $search_values[] = $search;
            $search_operators[] = '*';
        }
        $PHORUM['DATA']['SEARCH_DISPLAY_NAME'] =
            htmlspecialchars($search, ENT_COMPAT, $PHORUM["DATA"]["HCHARSET"]);
    }
    if (isset($_REQUEST['search_email'])) {
        $search = trim($_REQUEST['search_email']);
        if ($search != '') {
            $search_fields[] = 'email';
            $search_values[] = $search;
            $search_operators[] = '*';
        }
        $PHORUM['DATA']['SEARCH_EMAIL'] =
            htmlspecialchars($search, ENT_COMPAT, $PHORUM["DATA"]["HCHARSET"]);
    }

    // Find a list of matching user_ids to display on the current page.
    $user_ids = phorum_api_user_search(
        $search_fields, $search_values, $search_operators,
        TRUE, 'AND', '+username', 0, MAX_SEARCH_RESULTS+1
    );

    // Retrieve the user data for the users on the current page.
    if (!empty($user_ids)) {
        $users = phorum_api_user_get($user_ids, FALSE);
    }
}

// ----------------------------------------------------------------------
// Setup the template data
// ----------------------------------------------------------------------

if (count($users) > MAX_SEARCH_RESULTS) {
    $PHORUM['DATA']['TOO_MANY_USERS_FOUND'] = TRUE;
} elseif (isset($_POST['do_search']) && count($users) == 0) {
    $PHORUM['DATA']['NO_USERS_FOUND'] = TRUE;
}

$safe_users = array();
foreach ($users as $id => $user)
{
    $safe_users[$id] = array('user_id' => $user['user_id']);
    $safe_users[$id]['username'] = htmlspecialchars($user['username'], ENT_COMPAT, $PHORUM["DATA"]["HCHARSET"]);
    $safe_users[$id]['email'] = htmlspecialchars($user['email'], ENT_COMPAT, $PHORUM["DATA"]["HCHARSET"]);
    $safe_users[$id]['display_name'] = empty($PHORUM['custom_display_name']) 
        ? htmlspecialchars($user['display_name'])
        : $user['display_name'];

    // Add the impersonate user link to the user data.
    $checksum = phorum_mod_impersonate_user_generate_checksum($user['user_id']);
    $safe_users[$id]['URL']['IMPERSONATE_USER'] = phorum_get_url(
        PHORUM_ADDON_URL,
        "module=impersonate_user",
        "action=impersonate",
        "target=".$user['user_id'],
        "checksum=".urlencode($checksum)
    );

    // Add the link for the user's profile page.
    $safe_users[$id]['URL']['PROFILE'] = phorum_get_url(
        PHORUM_PROFILE_URL, $user['user_id']
    );

    // Add the link for sending the user a PM.
    $safe_users[$id]['URL']['PM'] = phorum_get_url(
        PHORUM_PM_URL, "page=send", "to_id=".$user['user_id']
    );
}

$PHORUM['DATA']['USERS'] = $safe_users;
$PHORUM['DATA']['USERS_COUNT'] = count($safe_users);

$data['template'] = 'impersonate_user::cc_panel';

?>
