<?php
    if (!defined("PHORUM_ADMIN")) return;

    include("./mods/signature_restrictions/defaults.php");

    // Save module settings to the database.
    if(count($_POST))
    {
        // Build new settings array.
        $settings = array();
        $settings["max_length"]      = (int) $_POST["max_length"];
        $settings["max_lines"]       = (int) $_POST["max_lines"];
        $settings["max_line_length"] = (int) $_POST["max_line_length"];
        $settings["deny_images"]     = isset($_POST["deny_images"]) ? 1 : 0;
        $settings["deny_markup"]     = isset($_POST["deny_markup"]) ? 1 : 0;
        $settings["markup_user_registered_days"]
                              = (int) $_POST["markup_user_registered_days"];

        // Take care of applying sane settings.
        if ($settings["max_length"] < 0) $settings["max_length"]=0;
        if ($settings["max_lines"] < 0) $settings["max_lines"]=0;
        if ($settings["max_lines_length"] < 0) $settings["max_lines_length"]=0;
        if ($settings["markup_user_registered_days"] < 0) 
                                    $settings["markup_user_registered_days"]=0;

        // Save settings array.
        $PHORUM["mod_signature_restrictions"] = $settings;
        phorum_db_update_settings(array(
            "mod_signature_restrictions" => $settings
        ));
        phorum_admin_okmsg("The module settings were successfully saved.");
    }

    include_once "./include/admin/PhorumInputForm.php";
    $frm = new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "signature_restrictions");

    $frm->addbreak("Edit settings for the signature restrictions module");

    $frm->addrow("Maximum length in total (0 = no restriction)", $frm->text_box('max_length', $PHORUM["mod_signature_restrictions"]["max_length"], 6) . ' characters');

    $frm->addrow("Maximum number of lines (0 = no restriction)", $frm->text_box('max_lines', $PHORUM["mod_signature_restrictions"]["max_lines"], 6) . ' lines');

    $frm->addrow("Maximum length per line (0 = no restriction)", $frm->text_box('max_line_length', $PHORUM["mod_signature_restrictions"]["max_line_length"], 6) . ' characters');

    $frm->addrow("Deny images in signatures", $frm->checkbox('deny_images', 1, "", $PHORUM["mod_signature_restrictions"]["deny_images"]) . 'Yes') ;
    
    $row = $frm->addrow(
        "Deny any markup in signatures",
        $frm->checkbox(
            'deny_markup', 1, "",
            $PHORUM["mod_signature_restrictions"]["deny_markup"]) .
            "Yes, unless user has been registered<br/>for at least " .
            $frm->text_box('markup_user_registered_days',
              $PHORUM["mod_signature_restrictions"]["markup_user_registered_days"], 6
            ) .
            " days (0 = deny for all users)");
    $frm->addhelp($row, "Deny any markup in signatures",
        "If this feature is enabled, then the user will only be allowed to
         use plain text in the signature. Formatting the signature is not
         allowed. Formatting could be done by using for example BBcode and/or
         HTML (in case respectively the BBcode and/or HTML module is
         enabled).<br/>
         <br/>
         This check is done by first formatting the signature in the same
         way as Phorum does when reading messages. After that, a check is
         done to see if there is HTML code in the signature. If it is, then
         the signature is denied."
    );

    $frm->show();

?>
