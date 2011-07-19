<?php

if (!defined("PHORUM")) return;

/**
 * Register the additional javascript code for this module.
 */
function phorum_mod_spellerpages_javascript_register($data)
{
    global $PHORUM;

    // Check what languages are candidate for loading a translated version.
    $languages = array();

    // For Phorum 5.3, we can have a list of compatible languages as setup
    // by the main language file. Check for each of this languages if we
    // can find a translated interface.
    if (!empty($PHORUM['compat_languages'])) {
      $languages = $PHORUM['compat_languages'];
    }
    // Before Phorum 5.3, we can only check the configured language.
    else {
      $languages[] = $PHORUM['language'];
    }

    // Check if a language-specific module template is available for one
    // of the languages. If yes, we will use that one. Otherwise, we
    // use the basic English version.
    $template = 'spellerpages::spellChecker.js';
    foreach ($languages as $language)
    {
      $check_template = "spellerpages::{$language}-spellChecker.js";
      $tpl = phorum_get_template_file($check_template);
      if (file_exists($tpl[2])) {
        $template = $check_template;
        break;
      }
    }

    $data[] = array(
        "module" => "spellerpages",
        "where"  => "after",
        "source" => "template($template)"
    );

    return $data;
}

/**
 * Add the spell check button to the editor.
 */
function phorum_mod_spellerpages_tpl_editor_buttons()
{ 
    global $PHORUM;
    include phorum_get_template('spellerpages::editor_button');
}

?>
