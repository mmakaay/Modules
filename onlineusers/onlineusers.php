<?php
if (!defined("PHORUM")) return;

// Load default settings for this module.
require_once('./mods/onlineusers/defaults.php');
require_once('./mods/onlineusers/db.php');

// Register the additional CSS code for this module.
function phorum_mod_onlineusers_css_register($data)
{
    $data['register'][] = array(
        "module" => "onlineusers",
        "where"  => "after",
        "source" => "template(onlineusers::css)"
    );
    return $data;
}

function phorum_mod_onlineusers_common_pre()
{
    global $PHORUM;

    // Check and handle automatic installation and upgrading
    // of the database structure. Do not continue running the
    // module in case the installation fails.
    if (! onlineusers_db_install()) return;

}

// This hook is used for tracking guest users.
function phorum_mod_onlineusers_common_post_user()
{
    global $PHORUM;

    // Check and handle automatic installation and upgrading
    // of the database structure. Do not continue running the
    // module in case the installation fails.
    if (! onlineusers_db_install()) return;    
    
    // No guest counting to do? Then we're done here.
    if (!empty($PHORUM["mod_onlineusers"]["disable_guests"])) return;

    // Create the guest track id.
    $ua = isset($_SERVER["HTTP_USER_AGENT"])
        ? $_SERVER["HTTP_USER_AGENT"] : 'unknown';
    $ip = isset($_SERVER["REMOTE_ADDR"])
        ? $_SERVER["REMOTE_ADDR"] : '127.0.0.1';
    
    // make it a raw binary hash for easier db access
    // TODO: 
    // somehow I can't get it to store the binary string into the field
    // therefore changed it to varchar(50), before it was binary(25)
    // and store a hex key
    //$track_id = sha1("{$ip}-{$ua}",true);
    $track_id = sha1("{$ip}-{$ua}");
    
    //print "Track_id: $track_id";

    // If we are handling an authenticated user, then see if we have to
    // delete a guest track record (in case the user just logged in).
    if ($PHORUM['user']['user_id']) {
    	//print "DEBUG: is user";
        if (onlineusers_db_search($track_id,'guest') !== NULL) {
            onlineusers_db_delete($track_id,'guest');
            phorum_cache_remove('onlineusers',$PHORUM['vroot']."-data");
        } else {
            return;
        }
    }
    else
    {
        // Skip some bots in the guest count.
        if (strstr($ua, "Google")       !== FALSE ||
            strstr($ua, "Yahoo! Slurp") !== FALSE ||
            strstr($ua, "-unknown")     !== FALSE) return;

        //print "DEBUG: is guest";    
        // Create a track record for the guest.
        onlineusers_db_insertreplace($track_id,'guest',time(),$PHORUM['forum_id'],0);
        
    }

}

// This hook is used for automatically displaying the online users at the
// top of the page.
function phorum_mod_onlineusers_after_header()
{
    return phorum_mod_onlineusers_before_footer(TRUE);
}

// This hook is used for automatically displaying the online users at the
// bottom of the page.
function phorum_mod_onlineusers_before_footer($is_header = FALSE)
{
    global $PHORUM;

    // make sure that we've got the db-table installed
    if(empty($PHORUM["mod_onlineusers_installed"])) {
    	return;    	
    }
    // If we are not on one of the pages where the user list has to be
    // shown, then we are done here.
    if ($is_header) {
        if (empty($PHORUM['mod_onlineusers']['show_pages'][phorum_page]) ||
            $PHORUM['mod_onlineusers']['show_pages'][phorum_page] != 2) {
            return;
        }
    } else {
        // Note: "in_array" is used for backward compatibility. From 2.4.5
        // on, the page selection settings were stored differently. 2.4.4
        // and before could only show the user list in the footer.
        if (!in_array(phorum_page, $PHORUM["mod_onlineusers"]["show_pages"]) &&
            (empty($PHORUM['mod_onlineusers']['show_pages'][phorum_page]) ||
             $PHORUM['mod_onlineusers']['show_pages'][phorum_page] != 1)) {
            return;
        }
    }

    // Check if the current user has rights to visit the forum.
    if (!empty($PHORUM['mod_onlineusers']['view_permission'])) {
        $perm = $PHORUM['mod_onlineusers']['view_permission'];
        if (($perm == ONLINEUSERS_PERM_REGISTEREDUSERS &&
             empty($PHORUM['user']['user_id'])) ||
            ($perm == ONLINEUSERS_PERM_ADMINS &&
             empty($PHORUM['user']['admin'])) ) return;
    }

    // If the cache has expired, if caching is disabled or if no
    // cached data is available yet, then update the list of online users.
    $do_update = true;
    if (!empty($PHORUM['mod_onlineusers']['cache_time'])) {
        $PHORUM['mod_onlineusers']['data'] = phorum_cache_get('onlineusers',$PHORUM['vroot']."-data");
        if($PHORUM['mod_onlineusers']['data'] !== NULL) {
            $do_update = false;
        }
    }
    
    if ($do_update) {
        mod_onlineusers_update();
        if (!empty($PHORUM['mod_onlineusers']['cache_time'])) {
            phorum_cache_put(
            	'onlineusers',
            	$PHORUM['vroot']."-data",
                $PHORUM['mod_onlineusers']['data'],
                $PHORUM['mod_onlineusers']['cache_time']
            );
        }
    }

    
    // Build the template data.

    include_once("./include/format_functions.php");
    $PHORUM['DATA']['MOD_ONLINEUSERS'] = array();
    $users = array();
    $hide_activity = 0;

    foreach ($PHORUM['mod_onlineusers']['data']['users'] as $user)
    {
        // For users that want some privacy, we count their activity
        // only as if they were a guest.
        if ($user['hide_activity']) {
            $hide_activity ++;
            continue;
        }

        // Format the user's idle time.
        $idle = 0;
        if ($PHORUM['user']['user_id'] &&
            $user['user_id'] == $PHORUM['user']['user_id']) {
            // NOOP: Don't show idle time for the active Phorum user.
        }
        elseif ($PHORUM['mod_onlineusers']['show_idle_time']) {
            $idle_secs = time() - $user['date_last_active'];
            if ($idle_secs) {
              $idle = sprintf("%d:%02d", floor($idle_secs/60), $idle_secs%60);
            }
        }

        $name = empty($PHORUM["custom_display_name"]) ? htmlspecialchars($user["display_name"], ENT_COMPAT, $PHORUM["DATA"]["HCHARSET"]) : $user["display_name"];

        $admin = empty($PHORUM['mod_onlineusers']['indicate_admin_users'])
               ? 0 : $user['admin'];

        $users[$user['user_id']] = array(
            'ID'      => $user['user_id'],
            'NAME'    => $name,
            'ADMIN'   => $admin,
            'PROFILE' => phorum_get_url(PHORUM_PROFILE_URL, $user['user_id']),
            'IDLE'    => $idle
        );
    }
    $PHORUM['DATA']['MOD_ONLINEUSERS']['USERS'] = $users;
    $PHORUM['DATA']['MOD_ONLINEUSERS']['USERCOUNT'] = count($users);

    if (empty($PHORUM["mod_onlineusers"]["disable_guests"])) {
        $PHORUM['DATA']['MOD_ONLINEUSERS']['GUESTCOUNT'] =
            $PHORUM['mod_onlineusers']['data']['guestcount'] + $hide_activity;
        $PHORUM['DATA']['MOD_ONLINEUSERS']['SHOW_GUESTS'] = TRUE;
    } else {
        $PHORUM['DATA']['MOD_ONLINEUSERS']['GUESTCOUNT'] = 0;
        $PHORUM['DATA']['MOD_ONLINEUSERS']['SHOW_GUESTS'] = FALSE;
    }

    $PHORUM['DATA']['MOD_ONLINEUSERS']['RECORD_USERCOUNT'] = 0;
    $PHORUM['DATA']['MOD_ONLINEUSERS']['RECORD_GUESTCOUNT'] = 0;
    if ($PHORUM['mod_onlineusers']['keep_records']) {
        if (!empty($PHORUM['mod_onlineusers']['data']['record_usercount'])) {
            $PHORUM['DATA']['MOD_ONLINEUSERS']['RECORD_USERCOUNT_DATE'] = phorum_date($PHORUM['long_date'], $PHORUM['mod_onlineusers']['data']['record_usercount_date']);
            $PHORUM['DATA']['MOD_ONLINEUSERS']['RECORD_USERCOUNT'] = $PHORUM['mod_onlineusers']['data']['record_usercount'];
        }
        if (!empty($PHORUM['mod_onlineusers']['data']['record_guestcount']) &&
            empty($PHORUM["mod_onlineusers"]["disable_guests"])) {
            $PHORUM['DATA']['MOD_ONLINEUSERS']['RECORD_GUESTCOUNT_DATE'] = phorum_date($PHORUM['long_date'], $PHORUM['mod_onlineusers']['data']['record_guestcount_date']);
            $PHORUM['DATA']['MOD_ONLINEUSERS']['RECORD_GUESTCOUNT'] = $PHORUM['mod_onlineusers']['data']['record_guestcount'];
        }
    }

    include(phorum_get_template('onlineusers::onlineusers'));
}

// Helper function for sorting users by their display name.
function mod_onlineusers_sort_by_name($a, $b)
{
    $a = strtolower($a['display_name']);
    $b = strtolower($b['display_name']);
    return strcmp($a, $b);
}

// This hook is used for adding the number of active readers in a forum
// to the forum index.
function phorum_mod_onlineusers_index($forums)
{
    global $PHORUM;

    if (empty($PHORUM['mod_onlineusers']['display_forum_readers'])) {
        return $forums;
    }

    $lang = $PHORUM['DATA']['LANG']['mod_onlineusers'];
    if (isset($PHORUM['mod_onlineusers']['data']['forum_reader_stats']))
    {
        $stats = $PHORUM['mod_onlineusers']['data']['forum_reader_stats'];
        foreach ($forums as $tempid => $forum)
        {
            // Skip adding reader counts for folders.
            if (!empty($forum['folder_flag'])) continue;

            // Allow specific language strings for specific amounts of
            // readers. I didn't simply use two forms here, because there
            // are some languages that might have different plural forms
            // for 2, 3 and more.
            $count = isset($stats[$forum['forum_id']])
                   ? (int) $stats[$forum['forum_id']] : 0;
            $str = isset($lang["UsersReadingThisForum$count"])
                 ? $lang["UsersReadingThisForum$count"]
                 : $lang['UsersReadingThisForum'];
            $str = str_replace('%count%', $count, $str);

            $forums[$tempid]['description'] .=
                "<span class=\"onlineusers_readers\">$str</span>";
        }
    }

    return $forums;
}

// Helper function for mod_onlineusers_update():
// Get the list of registered users.
function mod_onlineusers_get_user_list()
{
    $PHORUM = $GLOBALS["PHORUM"];

    $list = phorum_api_user_search(
        'date_last_active',
        time() - ($PHORUM["mod_onlineusers"]["idle_time"] * 60),
        '>=', TRUE
    );

    $activeusers = array();
    if (!empty($list))
    {
        $users = phorum_api_user_get($list);
        foreach ($users as $user)
        {
            $activeusers[$user['user_id']] = array(
                'user_id'           => $user['user_id'],
                'display_name'      => $user['display_name'],
                'admin'             => $user['admin'],
                'date_last_active'  => $user['date_last_active'],
                'last_active_forum' => $user['last_active_forum'],
                'hide_activity'     => $user['hide_activity']
            );
        }
    }

    // Sort the users by their name.
    if (count($activeusers) > 1) {
        uasort($activeusers, 'mod_onlineusers_sort_by_name');
    }

    // Allow the default user_list hook to act on the retrieved users.
    if (isset($PHORUM['hooks']['user_list']))
        $activeusers = phorum_hook('user_list', $activeusers);

    return $activeusers;
}

// Helper function for mod_onlineusers_update():
// Get the list of guests.
function mod_onlineusers_get_guest_list()
{
    global $PHORUM;

    // If we're not tracking guests, just return an empty array.
    if ($PHORUM['mod_onlineusers']['disable_guests']) return array();

    // get the guests from db
    $guests = onlineusers_db_get_entries('guest');

    // Cull the list by removing anybody who is past the idle time.
    $timeborder = time() - $PHORUM['mod_onlineusers']['idle_time'] * 60;
    $delete_ids = array();
    foreach ($guests as $id => $info) {
        if ($info['date_last_active'] < $timeborder) {
            $delete_ids[]=$id;
        }
    }
    if(count($delete_ids)) {
        onlineusers_db_delete($delete_ids,'guest');
    }

    return $guests;
}

// update data
function mod_onlineusers_update()
{
    global $PHORUM;
    $data = $PHORUM['mod_onlineusers']['data'];

    $data['users']       = mod_onlineusers_get_user_list();
    $data['usercount']   = count($data['users']);
    $data['guests']      = mod_onlineusers_get_guest_list();
    $data['guestcount']  = count($data['guests']);
    $data['update_time'] = time();

    // Update the visitor records.
    if ($PHORUM['mod_onlineusers']['keep_records'])
    {
        // we store it in our track table with another key
        // track_id = 1 -> guestcount
        // track_id = 2 -> usercount
        $records = onlineusers_db_get_entries('record_count');
        
        if (!empty($data['guestcount'])) {
            if(!isset($records[1]) || $data['guestcount'] > $records[1]['last_active_forum']) {
	            $data['record_guestcount'] = $data['guestcount'];
	            $data['record_guestcount_date'] = time();
	            onlineusers_db_insertreplace('1','record_count',$data['record_guestcount_date'],$data['record_guestcount'],0);
            } else {
                $data['record_guestcount'] = $records[1]['last_active_forum'];
                $data['record_guestcount_date'] = $records[1]['date_last_active'];               
            }
        }

        if (!empty($data['usercount'])) {
            if(!isset($records[2]) || $data['usercount'] > $records[2]['last_active_forum']) {
	            $data['record_usercount'] = $data['usercount'];
	            $data['record_usercount_date'] = time();
	            onlineusers_db_insertreplace('2','record_count',$data['record_usercount_date'],$data['record_usercount'],0);
            } else {
                $data['record_usercount'] = $records[2]['last_active_forum'];
                $data['record_usercount_date'] = $records[2]['date_last_active'];            	
            }
        }
    }

    // Update the list of people reading forums.
    if ($PHORUM["mod_onlineusers"]["display_forum_readers"])
    {
        $forums = array();

        // For all the users, increment whatever forum they were reading.
        foreach ($data["users"]  as $i) {
            if (isset($forums[$i["last_active_forum"]])) {
                $forums[$i["last_active_forum"]]++;
            } else {
                $forums[$i["last_active_forum"]] = 1;
            }
        }
        foreach ($data["guests"] as $i) {
            if (isset($forums[$i["last_active_forum"]])) {
                $forums[$i["last_active_forum"]]++;
            } else {
                $forums[$i["last_active_forum"]] = 1;
            }
        }

        $data['forum_reader_stats'] = $forums;
    }
    // Displaying forum readers is disabled.
    // Clean up the list of people reading forums.
    else {
        unset($data['forum_reader_stats']);
    }

    $PHORUM['mod_onlineusers']['data'] = $data;

}

?>
