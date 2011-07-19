<?php

if(!defined("PHORUM")) return;

function mod_remember_username_common()
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

    // Set the username field when entering the login page. We set it
    // here in the $_POST array, because we want the login script to set
    // the focus to the password field. If we would simply put it
    // in the Phorum DATA, the focus would stay on the username field.
    // This will however trigger an error message, because a login is
    // attempted. Therefore we set the phorum_mod_remember_username_active
    // variable, so we can flag the after_header hook function to
    // unset the error message.

    if ( phorum_page == 'login' &&
         ! isset($_POST["username"]) &&
         isset($_COOKIE["phorum_mod_remember_username"]) ) {

         $_POST["username"] = $_COOKIE["phorum_mod_remember_username"];
         $_POST["password"] = "";
         $_POST["phorum_mod_remember_username_active"] = 1;
    }

    // Because the login script will now process the login on the
    // first entry, the temporary cookie for checking for browser
    // cookie support will not be set. Start buffering the output,
    // so we can set the cookie ourselves from the after_header
    // hook function (else we get the PHP "headers already sent"
    // error message).

    ob_start();
}

function mod_remember_username_after_header()
{
    $PHORUM = $GLOBALS["PHORUM"];

    if (isset ($_POST["phorum_mod_remember_username_active"]))
    {
        // Reset the error message when entering the login page.
        unset($GLOBALS["PHORUM"]["DATA"]["ERROR"]);

        // Set the phorum_tmp_cookie.
        setcookie( 
            "phorum_tmp_cookie",
            "this will be destroyed once logged in", 0,
            $PHORUM["session_path"], $PHORUM["session_domain"]
        );

    }
}

?>
