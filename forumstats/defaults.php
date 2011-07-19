<?php

if (!isset($GLOBALS["PHORUM"]['mod_forumstats'])) {
    $GLOBALS["PHORUM"]['mod_forumstats'] = array();
}

if (!isset($GLOBALS["PHORUM"]["mod_forumstats"]["show_pages"])) {
    $GLOBALS["PHORUM"]["mod_forumstats"]["show_pages"] = array('index','list');
}

if (!isset($GLOBALS["PHORUM"]['mod_forumstats']['cache_time'])) {
    $GLOBALS["PHORUM"]['mod_forumstats']['cache_time'] = 60;
}

if (!isset($GLOBALS["PHORUM"]['mod_forumstats']['show_global'])) {
    $GLOBALS["PHORUM"]['mod_forumstats']['show_global'] = 1;
}

if (!isset($GLOBALS["PHORUM"]['mod_forumstats']['show_local'])) {
    $GLOBALS["PHORUM"]['mod_forumstats']['show_local'] = 1;
}

if (!isset($GLOBALS["PHORUM"]['mod_forumstats']['get_recent_user'])) {
    $GLOBALS["PHORUM"]['mod_forumstats']['get_recent_user'] = 1;
}

?>
