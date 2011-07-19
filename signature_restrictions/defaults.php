<?php

    if(!defined("PHORUM")) return;

    if (! isset($GLOBALS['PHORUM']["mod_signature_restrictions"])) {
         $GLOBALS['PHORUM']["mod_signature_restrictions"] = array();
    }

    $mod_signature_restrictions_default = array(
        "max_length"                  => 0,
        "max_lines"                   => 0,
        "max_line_length"             => 0,
        "deny_markup"                 => 0,
        "deny_images"                 => 0,
        "markup_user_registered_days" => 0
    );

    foreach ($mod_signature_restrictions_default as $var => $default) {
        if (! isset($GLOBALS["PHORUM"]["mod_signature_restrictions"][$var])) {
            $GLOBALS["PHORUM"]["mod_signature_restrictions"][$var] = $default;
        }
    }

?>
