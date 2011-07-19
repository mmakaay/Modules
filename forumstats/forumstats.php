<?php
/*  mod_forumstats: shows a couple of stats for a forum or globally
    author: Thomas Seifert (thomas@phorum.org)
*/
if (!defined('PHORUM')) return;

require_once('./mods/forumstats/defaults.php');

function mod_forumstats()
{
    $PHORUM=$GLOBALS["PHORUM"];

    // if we're not on one of our pages, then just don't do anything
    $page = phorum_page;
    if (!@in_array($page, $PHORUM["mod_forumstats"]["show_pages"])){
        return;
    }

    $data = NULL;
    $use_cache = 1;

    // only use the cache if cookies are enabled
    // otherwise the profile-url will contain session-data
    if(!$PHORUM['use_cookies'] && $PHORUM['mod_forumstats']['get_recent_user']) {
        $use_cache = 0;
    }

    // build the cache key
    $cache_key = $PHORUM['forum_id']."-".$PHORUM["mod_forumstats"]["show_global"].
                 "-".$PHORUM["mod_forumstats"]["show_local"];

    if($use_cache) {
        // build the cache key
        $cache_key = $PHORUM['forum_id']."-".$PHORUM["mod_forumstats"]["show_global"].
                     "-".$PHORUM["mod_forumstats"]["show_local"];
        $data = phorum_cache_get('mod_forumstats',$cache_key);
    }

    // build the stats, from the db or the cache
    if ($data == NULL){
            $data = mod_forumstats_getstats();

            if($use_cache) {
                $cache_time = isset($PHORUM['mod_forumstats']['cache_time'])?($PHORUM['mod_forumstats']['cache_time']*60):3600;
                phorum_cache_put('mod_forumstats',$cache_key,$data,$cache_time);
            }
    }

    // set the values for the template
    if($PHORUM["mod_forumstats"]["show_global"]) {
        $tpl_data = $PHORUM['DATA']['LANG']['Threads'].": ";
        $tpl_data.= number_format($data['global']['topics'], 0, "", $PHORUM["thous_sep"]).", ";
        $tpl_data.= $PHORUM['DATA']['LANG']['Posts'].": ";
        $tpl_data.= number_format($data['global']['posts'], 0, "", $PHORUM["thous_sep"]).", ";
        $tpl_data.= $PHORUM['DATA']['LANG']['mod_forumstats']['Users'].": ".number_format($data['global']['users'], 0, "", $PHORUM["thous_sep"]).".";
        $PHORUM['DATA']['mod_forumstats']['GlobalStatsLine'] =  $tpl_data;

        if($PHORUM['mod_forumstats']['get_recent_user']) {
            $PHORUM['DATA']['mod_forumstats']['recent_user_name']    = $data['recent_user_name'];
            $PHORUM['DATA']['mod_forumstats']['recent_user_profile'] = $data['recent_user_profile'];
        }

    }
    if($PHORUM["mod_forumstats"]["show_local"] && (isset($PHORUM["folder_flag"]) && !$PHORUM["folder_flag"])) {
        $tpl_data = $PHORUM['DATA']['LANG']['Threads'].": ";
        $tpl_data.= number_format($data['local']['topics'], 0, "", $PHORUM["thous_sep"]).", ";
        $tpl_data.= $PHORUM['DATA']['LANG']['Posts'].": ";
        $tpl_data.= number_format($data['local']['posts'], 0, "", $PHORUM["thous_sep"]).".";
        $PHORUM['DATA']['mod_forumstats']['LocalStatsLine'] =  $tpl_data;
    }

    include(phorum_get_template('forumstats::footer'));


    return;
}

function mod_forumstats_getstats()
{
    $PHORUM = $GLOBALS["PHORUM"];
    $statsdata=array();

    if($PHORUM["mod_forumstats"]["show_global"]) {
        $forums = phorum_db_get_forums();
        $topics = 0;
        $posts  = 0;
        foreach($forums as $id => $data) {
            $posts += $data['message_count'];
            $topics += $data['thread_count'];

        }
        $usercnt=phorum_db_user_count();
        $statsdata['global']=array('topics' => $topics, 'posts' => $posts, 'users' => $usercnt);
        if($PHORUM['mod_forumstats']['get_recent_user']) {
            $return = phorum_api_user_search(array('active'),array(PHORUM_USER_ACTIVE),array('='),TRUE,'AND','-date_added',0,1);
            if(!empty($return)) {
                $user_id = array_shift($return);
                $user_data = phorum_api_user_get($user_id,false);
                $statsdata['recent_user_name'] = $user_data['display_name'];
                $statsdata['recent_user_profile'] = phorum_get_url(PHORUM_PROFILE_URL,$user_id);
            }
        }
    }
    if($PHORUM["mod_forumstats"]["show_local"] && (isset($PHORUM["folder_flag"]) && !$PHORUM["folder_flag"])) {
        $statsdata['local']=array('topics' => $PHORUM['thread_count'], 'posts' => $PHORUM['message_count']);
    }

    return $statsdata;
}
?>
