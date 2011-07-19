<?php
if (!defined('PHORUM')) return;

// The version of the FancyBox library.
// This versions must match the version number of the library file
// as it can be found in the code subdirectory.
define('FANCYBOX_VERSION', '1.3.4');

define('FANCYBOX_PATH', 'mods/embed_images/viewers/fancybox/code');

function image_viewer_css_register($data)
{
    global $PHORUM;

    $css = dirname(__FILE__) . '/code/' .
           'jquery.fancybox-' . FANCYBOX_VERSION . '.css';

    if (empty($PHORUM['mod_embed_images']['fancybox_noload'])) {
        $data['register'][] = array(
            'module'    => 'embed_images/viewer:fancybox',
            'where'     => 'after',
            'source'    => 'function(fancybox_get_css)',
            'cache_key' => filemtime(__FILE__) . filemtime($css)
        );
    }

    return $data;
}

/**
 * A function for retrieving the FancyBox CSS code.
 * We need a function, because the FancyBox CSS code depends a lot
 * on relative file positions. Here, we load the CSS code and turn
 * all image references into absolute URLs.
 *
 * @return string
 */
function fancybox_get_css()
{
    global $PHORUM;

    $css = dirname(__FILE__) . '/code/' .
           'jquery.fancybox-' . FANCYBOX_VERSION . '.css';
    $code = file_get_contents($css);
    $url  = $PHORUM['http_path'] . '/' . FANCYBOX_PATH;

    // Update all background-image url references to use an absolute URL.
    $code = preg_replace(
        '/\burl\(\'?([\w-]+\.\w+)\'?\)/',
        "url('$url/\$1')",
        $code
    );

    // Update all src='fancybox/someimage.png' references.
    $code = preg_replace(
        '/src=\'?fancybox\/(\w+\.\w+)\'/',
        "src='$url/\$1'",
        $code
    );

    return $code;
}

function image_viewer_javascript_register($data)
{
    $PHORUM = $GLOBALS['PHORUM'];

    if (empty($PHORUM['mod_embed_images']['fancybox_noload'])) {
        $data[] = array(
            'module' => 'embed_images/viewer:fancybox',
            'source' => 'file(' . FANCYBOX_PATH . '/jquery.fancybox-' .
                        FANCYBOX_VERSION . '.pack.js)'
        );
    }

    $data[] = array(
        'module' => 'embed_images/viewer:fancybox',
        'source' => "file(mods/embed_images/viewers/fancybox/viewer.js)"
    );

    return $data;
}

?>
