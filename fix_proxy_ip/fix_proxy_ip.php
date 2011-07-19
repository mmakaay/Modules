<?php

if (!defined("PHORUM")) return;

function phorum_mod_fix_proxy_ip()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
}

?>
