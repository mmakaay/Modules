<?php

if(!defined("PHORUM")) return;

function phorum_mod_disable_cancel_in_posting($data)
{
    $GLOBALS["PHORUM"]["DATA"]["SHOW_CANCEL_BUTTON"] = FALSE;
    return $data;
}

?>
