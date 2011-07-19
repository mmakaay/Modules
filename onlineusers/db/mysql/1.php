<?php

if (!defined("PHORUM")) return;

$sqlqueries[]= "
   CREATE TABLE {$PHORUM["onlineusers_tracking_table"]} (
      vroot             int(10) unsigned            NOT NULL,
      `type`            set('guest','record_count') NOT NULL,
      track_id          varchar(50)                 NOT NULL,
      date_last_active  int(10) unsigned            NOT NULL,
      last_active_forum int(10) unsigned            NOT NULL,
      hide_activity     tinyint(4)                  NOT NULL default '0',
  PRIMARY KEY  (vroot,`type`,track_id)
)
";

?>
