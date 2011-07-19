<?php

if(!defined("PHORUM")) return;

// The database schema version, which is used to handle
// installation and upgrades directly from the module.
define("ONLINEUSERS_DB_VERSION", 1);

// The table name for storing event logs.
$GLOBALS["PHORUM"]["onlineusers_tracking_table"] =
    "{$GLOBALS["PHORUM"]["DBCONFIG"]["table_prefix"]}_onlineusers_track";

/*
CREATE TABLE `phorum_onlineusers_track` (
`vroot` INT UNSIGNED NOT NULL ,
`type` VARCHAR( 255 ) NOT NULL ,
`track_id` VARCHAR( 255 ) NOT NULL ,
`date_last_active` INT UNSIGNED NOT NULL ,
`last_active_forum` INT UNSIGNED NOT NULL ,
`hide_activity` TINYINT NOT NULL DEFAULT '0',
PRIMARY KEY ( `vroot` , `type` , `track_id` )
) ENGINE = MYISAM ;

 */
/**
 * This function will check if an upgrade of the database scheme is needed.
 * It is generic for all database layers.
 */
function onlineusers_db_install()
{
    global $PHORUM;

    $version = isset($PHORUM["mod_onlineusers_installed"])
        ? $PHORUM["mod_onlineusers_installed"] : 0;

    while ($version < ONLINEUSERS_DB_VERSION)
    {
        // Initialize the settings array that we will be saving.
        $version++;
        $settings = array( "mod_onlineusers_installed" => $version );

        $sqlfile = "./mods/onlineusers/db/" .
                   $PHORUM["DBCONFIG"]["type"] . "/$version.php";

        if (! file_exists($sqlfile)) {
            print "<b>Unexpected situation on installing " .
                  "the Onlineusers module</b>: " .
                  "unable to find the database schema setup script " .
                  htmlspecialchars($sqlfile);
            return false;
        }

        $sqlqueries = array();
        include($sqlfile);

        if (count($sqlqueries) == 0) {
            print "<b>Unexpected situation on installing " .
                  "the Onlineusers module</b>: could not read any SQL " .
                  "queries from file " . htmlspecialchars($sqlfile);
            return false;
        }
        $err = phorum_db_run_queries($sqlqueries);
        if ($err) {
            print "<b>Unexpected situation on installing " .
                  "the Onlineusers module</b>: running the " .
                  "install queries from file " . htmlspecialchars($sqlfile) .
                  " failed. The error was " . htmlspecialchars($err);
            return false;
        }
        // for old installs, remove the data-part of onlineusers
        if(isset($PHORUM['mod_onlineusers']['data'])) {
        	unset($PHORUM['mod_onlineusers']['data']);
        	$settings['mod_onlineusers']=$PHORUM['mod_onlineusers'];
        }
        
        // Save our settings.
        if (!phorum_db_update_settings($settings)) {
            print "<b>Unexpected situation on installing " .
                  "the Onlineusers module</b>: updating the " .
                  "mod_onlineusers_installed setting failed";
            return false;
        }
        

           
    }

    return true;
}

/**
 * Delete a guest tracking entry from the onlineusers table
 *
 * @param $track_id - A hash containing the track-id of the guest user
 *
 */
function onlineusers_db_delete($track_id,$type='guest')
{
    global $PHORUM;
    
    if(is_array($track_id)) {
        $track_cond = "IN('".implode("','",$track_id)."')";
    } else {
        $track_cond = "='$track_id'";
    }

    // Insert the logging record in the database.
    phorum_db_interact(
        DB_RETURN_RES,
        "DELETE FROM {$PHORUM["onlineusers_tracking_table"]}
         WHERE vroot = {$PHORUM['vroot']} AND type='$type' AND track_id $track_cond",
         NULL,
         DB_MASTERQUERY
    );
}


/**
 * Return the entry found for the given track-id
 *
 * @param $track_id - A hash containing the track-id of the user
 * @return $row - The entry found for the track_id
 */
function onlineusers_db_search($track_id,$type='guest')
{
    $PHORUM = $GLOBALS["PHORUM"];

    $row = phorum_db_interact(
        DB_RETURN_ASSOC,
        "SELECT *
         FROM {$PHORUM["onlineusers_tracking_table"]}
         WHERE vroot = {$PHORUM['vroot']} AND type = '$type' AND track_id ='$track_id'"
    );

    return $row;
}

/**
 * Return the entrys found for the given type and vroot
 *
 * @param $type - type of entries
 * @return $row - The entries found for the type
 */
function onlineusers_db_get_entries($type='guest')
{
    global $PHORUM;

    $rows = phorum_db_interact(
        DB_RETURN_ASSOCS,
        "SELECT *
         FROM {$PHORUM["onlineusers_tracking_table"]}
         WHERE vroot = {$PHORUM['vroot']} AND type = '$type'",
         'track_id'
    );

    return $rows;
}

/**
 * insert the onlineusers entry with the given data
 *
 * @param $track_id - A hash containing the track-id of the guest user
 * @return $row - The entry found for the track_id
 */
function onlineusers_db_insertreplace($track_id,$type='guest',$date_last_active,$last_active_forum,$hide_activity)
{
    global $PHORUM;

    $row = phorum_db_interact(
        DB_RETURN_ERROR,
        "REPLACE INTO {$PHORUM["onlineusers_tracking_table"]}
         (vroot,type,track_id,date_last_active,last_active_forum,hide_activity)
         VALUES ({$PHORUM['vroot']},'$type','$track_id',$date_last_active,$last_active_forum,$hide_activity)"
    );
    
    //print_var($row);

    return $row;
}

?>
