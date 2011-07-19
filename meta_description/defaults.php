<?php
// Apply default values for the meta description module settings.

if (!isset($GLOBALS["PHORUM"]["mod_meta_description"]["excerpt_paragraphs"]))
    $GLOBALS["PHORUM"]["mod_meta_description"]["excerpt_paragraphs"] = 2;

if (!isset($GLOBALS["PHORUM"]["mod_meta_description"]["excerpt_words"]))
    $GLOBALS["PHORUM"]["mod_meta_description"]["excerpt_words"] = 100;

if (!isset($GLOBALS["PHORUM"]["mod_meta_description"]["excerpt_characters"]))
    $GLOBALS["PHORUM"]["mod_meta_description"]["excerpt_characters"] = 0;

?>
