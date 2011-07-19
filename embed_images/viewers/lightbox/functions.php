<?php
if (!defined('PHORUM')) return;

function image_viewer_css_register($data)
{
    $PHORUM = $GLOBALS['PHORUM'];

    if (empty($PHORUM["mod_embed_images"]["lightbox_noload"])) {
        $data['register'][] = array(
            'module' => 'embed_images/viewer:lightbox',
            'where'  => 'after',
            'source' => 'file(mods/embed_images/viewers/lightbox/code/css/lightbox.css)'
        );
    }

    return $data;
}

function image_viewer_javascript_register($data)
{
    $PHORUM = $GLOBALS['PHORUM'];

    if (empty($PHORUM['mod_embed_images']['lightbox_noload_prototype'])) {
        $data[] = array(
            'module' => 'embed_images/viewer:lightbox',
            'source' => 'file(mods/embed_images/viewers/lightbox/code/js/prototype.js)'
        );
    }

    if (empty($PHORUM['mod_embed_images']['lightbox_noload_scriptaculous'])) {
        $data[] = array(
            'module' => 'embed_images/viewer:lightbox',
            'source' => 'file(mods/embed_images/viewers/lightbox/code/js/scriptaculous.js)'
        );
        $data[] = array(
            'module' => 'embed_images/viewer:lightbox',
            'source' => 'file(mods/embed_images/viewers/lightbox/code/js/effects.js)'
        );
    }

    if (empty($PHORUM['mod_embed_images']['lightbox_noload'])) {
        $data[] = array(
            'module' => 'embed_images/viewer:lightbox',
            'source' => 'file(mods/embed_images/viewers/lightbox/code/js/lightbox.js)'
        );
    }

    $data[] = array(
        'module' => 'embed_images/viewer:lightbox',
        'source' => "file(mods/embed_images/viewers/lightbox/viewer.js)"
    );

    return $data;
}


function image_viewer_common()
{
    global $PHORUM;

    if (!empty($PHORUM["mod_embed_images"]["lightbox_noload"])) {
        return;
    }

    // Lightbox JavaScript and stylesheet overrides.
    $images = $PHORUM['http_path'] .
              "/mods/embed_images/viewers/lightbox/code/images";
    $PHORUM['DATA']['HEAD_TAGS'] .=
        "<script type=\"text/javascript\">\n" .
        "  fileLoadingImage = '$images/loading.gif';\n" .
        "  fileBottomNavCloseImage = '$images/close.gif';\n" .
        "</script>" .
        "<style type=\"text/css\">\n" .
        "  #imageData #bottomNavClose{ text-align:right; }\n" .
        "  #prevLink, #nextLink{\n" .
        "    background: transparent url($images/blank.gif) no-repeat;\n" .
        "  }\n" .
        "  #prevLink:hover, #prevLink:visited:hover {\n" .
        "    background: url($images/prevlabel.gif) left 15% no-repeat;\n" .
        "  }\n" .
        "  #nextLink:hover, #nextLink:visited:hover {\n" .
        "    background: url($images/nextlabel.gif) right 15% no-repeat;\n" .
        "  }\n" .
        "</style>\n";
}

?>
