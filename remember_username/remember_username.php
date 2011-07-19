<?php

if(!defined("PHORUM")) return;

function mod_remember_username_after_login($data)
{
    $PHORUM = $GLOBALS["PHORUM"];

    // Remember the username for a logged in user using a cookie.
    if ($PHORUM["DATA"]["LOGGEDIN"])
    {
        $username = $PHORUM["user"]["username"];

        // No cookie set or cookie differs? Then set a new cookie.
        if ( !isset($_COOKIE["phorum_mod_remember_username"]) ||
             $_COOKIE["phorum_mod_remember_username"] != $username )
        {
            setcookie(
                "phorum_mod_remember_username",
                $PHORUM["user"]["username"],
                time() + 315360000,  // expire cookie after 10 years
                $PHORUM["session_path"], $PHORUM["session_domain"]
            );
        }
    }

    return $data;
}

function mod_remember_username_start_output()
{
    global $PHORUM;

    // Are are entering the login page?
    if (phorum_page == 'login' && ! isset($_POST["username"]) &&
        isset($_COOKIE["phorum_mod_remember_username"]))
    {
         // Replace the username with the remembered username.
         $PHORUM['DATA']['LOGIN']['username'] =
             $_COOKIE["phorum_mod_remember_username"];

         // Make the focus shift to the password field.
         $PHORUM['DATA']['FOCUS_TO_ID'] = 'password';
    }
}

?>
