<?php

if (!defined('PHORUM')) return;

if (!isset($GLOBALS['PHORUM']['mod_whitespace_restrictions'])) {
     $GLOBALS['PHORUM']['mod_whitespace_restrictions'] = array();
}

if (!isset($GLOBALS['PHORUM']['mod_whitespace_restrictions']['limit'])) {
    $GLOBALS['PHORUM']['mod_whitespace_restrictions']['limit'] = 5;
}

?>
