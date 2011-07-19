<?php

if (!defined('PHORUM')) return;

require_once('./mods/show_moderators/defaults.php');

// Generate the template data for the index page and handle automatic
// displaying of moderators on the index page.
function phorum_mod_show_moderators_index($data)
{
    $PHORUM = $GLOBALS['PHORUM'];

    // Determine the template file to use for automatic displaying.
    if (!empty($PHORUM['mod_show_moderators']['show_on_index'])) {
        $template = phorum_get_template('show_moderators::index');
    }

    foreach ($data as $id => $forum)
    {
        if (empty($forum['folder_flag']))
        {
            // Retrieve the list of moderators.
            $moderators = mod_show_moderators_get_list($forum['forum_id']);

            // Add the moderator list in the forum data.
            $data[$id]['MODERATORS'] = $moderators;
            $data[$id]['MODERATOR_COUNT'] = count($moderators);

            // In case automatic displaying is enabled, then add the
            // moderator(s) to the message description.
            if (!empty($PHORUM['mod_show_moderators']['show_on_index']))
            {
                $PHORUM['DATA']['MODERATORS'] = $moderators;
                $PHORUM['DATA']['MODERATOR_COUNT'] = count($moderators);
                ob_start();
                include($template);
                $data[$id]['description'] .= ob_get_contents();
                ob_end_clean();
            }
        }
    }
    return $data;
}

// Generate the template data for the list page.
function phorum_mod_show_moderators_list($data)
{
    $PHORUM = $GLOBALS['PHORUM'];
    $moderators = mod_show_moderators_get_list($PHORUM['forum_id']);
    $GLOBALS['PHORUM']['DATA']['MODERATORS'] = $moderators;
    $GLOBALS['PHORUM']['DATA']['MODERATOR_COUNT'] = count($moderators);

    return $data;
}

// Generate the template data for the read page.
function phorum_mod_show_moderators_read($data)
{
    $PHORUM = $GLOBALS['PHORUM'];
    $moderators = mod_show_moderators_get_list($PHORUM['forum_id']);
    $GLOBALS['PHORUM']['DATA']['MODERATORS'] = $moderators;
    $GLOBALS['PHORUM']['DATA']['MODERATOR_COUNT'] = count($moderators);

    return $data;
}

// Handle automatic displaying of moderators for the list and read pages.
function phorum_mod_show_moderators_before_footer()
{
    global $PHORUM;
    if ( (phorum_page == 'list' &&
          !empty($PHORUM['mod_show_moderators']['show_on_list'])) ||
         (phorum_page == 'read' &&
          !empty($PHORUM['mod_show_moderators']['show_on_read'])) ) {

        include(phorum_get_template('show_moderators::footer'));
    }
}

/**
 * Retrieve the moderators for a given forum.
 *
 * @param integer $forum_id
 *     The id of the forum for which to retrieve the moderator list.
 *
 * @return array
 *     An array of moderators for the forum.
 *
 */
function mod_show_moderators_get_list($forum_id)
{
    $PHORUM = $GLOBALS['PHORUM'];

    $no_admins  = empty($PHORUM['mod_show_moderators']['include_admins']) ? 1:0;
    $cache_type = 'show_moderators';
    $cache_key  = $forum_id . '-' . $no_admins;
    $moderators = NULL;

    // Try to retrieve the moderator list from the cache.
    if (!empty($PHORUM['mod_show_moderators']['cache'])) {
        $moderators = phorum_cache_get($cache_type, $cache_key);
    }

    // No info read from cache. Load the list from the database.
    if ($moderators === NULL)
    {
        $moderators = phorum_api_user_list_moderators($forum_id, $no_admins);

        if (!empty($moderators)) {
            $users = phorum_api_user_get(array_keys($moderators));
            $moderators = array();
            foreach($users as $id => $user) {
                $display_name = empty($PHORUM["custom_display_name"]) ? htmlspecialchars($user["display_name"], ENT_COMPAT, $PHORUM["DATA"]["HCHARSET"]) : $user["display_name"];
                $profile_url = phorum_get_url(PHORUM_PROFILE_URL, $id);
                $moderators[$id] = array(
                    'DISPLAY_NAME' => $display_name,
                    'PROFILE'      => $profile_url
                );
            }
        }

        // Store the list in the cache.
        if (!empty($PHORUM['mod_show_moderators']['cache'])) {
            $ttl = $PHORUM['mod_show_moderators']['cache'];
            phorum_cache_put($cache_type, $cache_key, $moderators, $ttl);
        }
    }

    return $moderators;
}

?>
