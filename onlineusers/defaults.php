<?php

# Some defines for the permission system.

define('ONLINEUSERS_PERM_ALL', 0);
define('ONLINEUSERS_PERM_REGISTEREDUSERS', 1);
define('ONLINEUSERS_PERM_ADMINS', 3);

# Some default settings for the online users module.

if (!isset($GLOBALS['PHORUM']["mod_onlineusers"]["idle_time"])) {
    $GLOBALS['PHORUM']["mod_onlineusers"]["idle_time"] = 10;
}

if (!isset($GLOBALS['PHORUM']["mod_onlineusers"]["cache_time"])) {
    $GLOBALS['PHORUM']["mod_onlineusers"]["cache_time"] = 60;
}

if (!isset($GLOBALS['PHORUM']["mod_onlineusers"]["show_pages"])) {
    $GLOBALS['PHORUM']["mod_onlineusers"]["show_pages"] = array(
        'index' => 1,
        'list'  => 1,
        'read'  => 1
    );
}

if (!isset($GLOBALS['PHORUM']["mod_onlineusers"]["disable_guests"])) {
    $GLOBALS['PHORUM']["mod_onlineusers"]["disable_guests"] = 0;
}

if (!isset($GLOBALS['PHORUM']["mod_onlineusers"]["show_idle_time"])) {
    $GLOBALS['PHORUM']["mod_onlineusers"]["show_idle_time"] = 0;
}

if (!isset($GLOBALS['PHORUM']["mod_onlineusers"]["keep_records"])) {
    $GLOBALS['PHORUM']["mod_onlineusers"]["keep_records"] = 1;
}

if (!isset($GLOBALS['PHORUM']["mod_onlineusers"]["display_forum_readers"])) {
    $GLOBALS['PHORUM']["mod_onlineusers"]["display_forum_readers"] = 1;
}

if (!isset($GLOBALS['PHORUM']["mod_onlineusers"]["indicate_admin_users"])) {
    $GLOBALS['PHORUM']["mod_onlineusers"]["indicate_admin_users"] = 0;
}

if (!isset($GLOBALS['PHORUM']["mod_onlineusers"]["view_permission"])) {
    $GLOBALS['PHORUM']["mod_onlineusers"]["view_permission"] = ONLINEUSERS_PERM_ALL;
}

?>
