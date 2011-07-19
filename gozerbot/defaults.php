<?php

if (!isset($GLOBALS['PHORUM']['mod_gozerbot'])) {
    $GLOBALS['PHORUM']['mod_gozerbot'] = array();
}

foreach (array(
      'host'           => 'localhost',
      'port'           => '5500',
      'password'       => '',
      'cryptkey'       => '',
      'target'         => '#channel',
      'max_words'      => 20,
      'use_tinyurl'    => 0,
      'full_path'      => 1,
      'do_new_threads' => 1,
      'do_new_replies' => 1
    ) as $key => $val) {
    if (!isset($GLOBALS['PHORUM']['mod_gozerbot'][$key]) ||
        $GLOBALS['PHORUM']['mod_gozerbot'][$key] === '') {
        $GLOBALS['PHORUM']['mod_gozerbot'][$key] = $val;
    }
}

?>
