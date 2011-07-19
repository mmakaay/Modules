<?php

// Apply default values for the module settings.

if (!isset($GLOBALS['PHORUM']['mod_embed_images'])) {
    $GLOBALS['PHORUM']['mod_embed_images'] = array();
}

foreach (array(
    'max_width'     => 200,
    'max_height'    => 200,
    'handle_bbcode' => 1,
    'handle_html'   => 1,
    'handle_url'    => 1,
    'cache_dir'     => '',
    'cache_url'     => '',
    'image_viewer'  => 'jquery_fancybox',
    'embed_bbcode'  => 1,
    'embed_html'    => 1,
    'embed_url'     => 1,
    'embed_embatt'  => 1,
    'embed_allatt'  => 0) as $key => $val) {

    if (!isset($GLOBALS['PHORUM']['mod_embed_images'][$key])) {
        $GLOBALS['PHORUM']['mod_embed_images'][$key] = $val;
    }
}

?>
