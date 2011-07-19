<?php
if (!defined('PHORUM')) return;

// The version of the jQuery Lightbox code. This version must match the
// version number as used in the code directory files.
define('JQUERY_LIGHTBOX_VERSION', '0.5');

function image_viewer_css_register($data)
{
    $PHORUM = $GLOBALS['PHORUM'];

    if (empty($PHORUM["mod_embed_images"]["jquery_lightbox_noload"])) {
        $data['register'][] = array(
            'module' => 'embed_images/viewer:jquery_lightbox',
            'where'  => 'after',
            'source' => 'file(mods/embed_images/viewers/jquery_lightbox/code/css/jquery.lightbox-' . JQUERY_LIGHTBOX_VERSION . '.css)'
        );
    }

    return $data;
}

function image_viewer_javascript_register($data)
{
    $PHORUM = $GLOBALS['PHORUM'];

    if (empty($PHORUM['mod_embed_images']['jquery_lightbox_noload'])) {
        $data[] = array(
            'module' => 'embed_images/viewer:jquery_lightbox',
            'source' => 'file(mods/embed_images/viewers/jquery_lightbox/code/js/jquery.lightbox-' . JQUERY_LIGHTBOX_VERSION . '.pack.js)'
        );
    }

    $data[] = array(
        'module' => 'embed_images/viewer:jquery_lightbox',
        'source' => "file(mods/embed_images/viewers/jquery_lightbox/viewer.js)"
    );

    return $data;
}

// Setup Phorum.http_path javascript variable. Available by default in
// Phorum 5.3, but added here for backward compatibility with Phorum 5.2.
function image_viewer_after_header()
{
    global $PHORUM;

    print "<script type=\"text/javascript\">\n";
    print "Phorum.http_path = '{$PHORUM['http_path']}';\n"; 
    print "</script>\n";
}

?>
