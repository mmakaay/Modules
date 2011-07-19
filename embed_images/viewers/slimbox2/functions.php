<?php

if (!defined('PHORUM')) return;

function image_viewer_css_register($data)
{
    $PHORUM = $GLOBALS['PHORUM'];

    if (empty($PHORUM["mod_embed_images"]["slimbox2_noload"])) {
        $data['register'][] = array(
            'module' => 'embed_images/viewer:slimbox2',
            'where'  => 'after',
            'source' => 'file(mods/embed_images/viewers/slimbox2/code/css/slimbox.css)'
        );
    }

    return $data;
}

function image_viewer_javascript_register($data)
{
    $PHORUM = $GLOBALS['PHORUM'];

    if (empty($PHORUM['mod_embed_images']['slimbox2_mootools_noload'])) {
        $data[] = array(
            'module' => 'embed_images/viewer:slimbox2',
            'source' => 'file(mods/embed_images/viewers/slimbox2/code/js/mootools.js)'
        );
    }

    if (empty($PHORUM['mod_embed_images']['slimbox2_noload'])) {
        $data[] = array(
            'module' => 'embed_images/viewer:slimbox2',
            'source' => 'file(mods/embed_images/viewers/slimbox2/code/js/slimbox.js)'
        );
    }

    $data[] = array(
        'module' => 'embed_images/viewer:slimbox2',
        'source' => 'file(mods/embed_images/viewers/slimbox2/viewer.js)'
    );

    return $data;
}


function image_viewer_common()
{
    global $PHORUM;

    if (!empty($PHORUM["mod_embed_images"]["slimbox2_noload"])) {
        return;
    }

    // Lightbox JavaScript and stylesheet overrides.
    $images = $PHORUM['http_path'] .
              "/mods/embed_images/viewers/slimbox2/code/css";
    $PHORUM['DATA']['HEAD_TAGS'] .=
        "<style type=\"text/css\">\n" .
        "  .lbLoading {\n" .
        "    background: #fff url($images/loading.gif) no-repeat center;\n" .
        "  }\n" .
        "  #lbPrevLink:hover {\n" .
        "    background: transparent url($images/prevlabel.gif) no-repeat 0% 15%;\n" .
        "  }\n" .
        "  #lbNextLink:hover {\n" .
        "    background: transparent url($images/nextlabel.gif) no-repeat 100% 15%;\n" .
        "  }\n" .
        "  #lbCloseLink {\n" .
        "    background: transparent url($images/closelabel.gif) no-repeat center;\n" .
        "  }\n" .
        "</style>\n";
}

?>
