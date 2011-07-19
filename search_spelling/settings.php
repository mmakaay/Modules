<?php

if (!defined("PHORUM_ADMIN")) return;

// Apply some default values to the settings.
if (!isset($PHORUM['mod_search_spelling']['language'])) {
    $PHORUM['mod_search_spelling']['language'] = '';
}
if (!isset($PHORUM['mod_search_spelling']['auto_display'])) {
    $PHORUM['mod_search_spelling']['auto_display'] = 1;
}

$languages = array('' => 'Any language');

// Try to retrieve the available languages from the Google site.
include "./include/api/http_get.php";
$google_url = 'http://www.google.com/advanced_search?hl=en';
$page = phorum_api_http_get($google_url);
if (!empty($page))
{
    if (preg_match_all('/<option value=\"lang_(\w+)\"\s*>([^<>]+)/', $page, $m)) {
        foreach ($m[1] as $id => $lang) {
            $languages[$lang] = $m[2][$id];
        }
    }
}

// Save module settings to the database.
if(count($_POST))
{
    // Check which language to use. If the language is not in the list
    // of retrieved languages, then fallback to "Any language".
    $lang = $_POST['language'];
    if (!empty($languages) && empty($languages[$lang])) {
        $lang = '';
    }

    $settings = array(
        "language"     => $lang,
        "auto_display" => isset($_POST['auto_display']) ? 1 : 0
    );

    $PHORUM["mod_search_spelling"] = $settings;
    phorum_db_update_settings(array(
        "mod_search_spelling" => $settings
    ));
    phorum_admin_okmsg("The module settings were successfully saved.");
}

print '<div style="font-size: 18px; text-align:right">';
print '<img align="top" src="'.$PHORUM['http_path'].'/mods/search_spelling/google.gif"/> powered';
print '</div>';

include_once "./include/admin/PhorumInputForm.php";
$frm = new PhorumInputForm ("", "post", "Save");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "search_spelling"); 

$frm->addbreak("Edit settings for the Search Spelling module");

if (empty($page)) {
  $frm->addrow(
      "Retrieving <a href=\"$google_url\">the advanced Google search page</a>
       failed.<br/> This could mean that your PHP setup does not support this
       module.<br/>
       <br/>
       To be able to query the Google web site, PHP must be configured to<br/>
       allow opening of URLs for file reading commands. The php.ini<br/>
       option \"allow_url_fopen\" must be set to 1 for this. If this option<br/>
       is disabled, then this module will not work."
  );
} else {
  $frm->addrow(
      "Select the language to use or \"Any language\" if<br/>" .
      "the language that you require is not available.",
       $frm->select_tag(
           "language",
           $languages,
           $PHORUM['mod_search_spelling']['language']
       )
  );

  $row = $frm->addrow(
      'Automatically display the "Did you mean ..." link?"',
      $frm->checkbox(
          'auto_display', 1, 'Yes',
          $PHORUM['mod_search_spelling']['auto_display']
      )
  );
  $frm->addhelp($row,
      'Automatically display the "Did you mean ..." link?',
      "If you enable this option, then the module will automatically
       display the \"Did you mean ...\" link after displaying the
       page header. For most installations, this should be fine.<br/>
       <br/>
       If you disable the option, you can modify your templates
       to show the link at the exact spot and using the exact styling
       that you find appropriate. You can use the following template
       variables:<br/>
       <ul>
         <li> <b>Strings from the module language file:</b><br/>
              {LANG->mod_search_spelling->DidYouMeanPre}<br/>
              {LANG->mod_search_spelling->DidYouMeanPost}</li>
         <li> <b>The alternative search query proposal</b><br/>
              {SEARCH_SPELLING->QUERY}</li>
         <li> <b>The URL to run the alternative search query</b><br/>
              {SEARCH_SPELLING->URL}</li>
       </ul>"
  );
}

$frm->show();
?>

