<?php

function image_viewer_css_register($data)
{
    if (empty($GLOBALS['PHORUM']["mod_embed_images"]["dynadrive_noload"])) {
        $data['register'][] = array(
            'module' => 'embed_images/viewer:dynadrive',
            'where'  => 'after',
            'source' => 'file(mods/embed_images/viewers/dynadrive/code/thumbnailviewer.css)'
        );
    }

    return $data;
}

function image_viewer_javascript_register($data)
{
    if (empty($GLOBALS['PHORUM']["mod_embed_images"]["dynadrive_noload"])) {
        $data[] = array(
            'module' => 'embed_images/viewer:dynadrive',
            'source' => 'file(mods/embed_images/viewers/dynadrive/code/thumbnailviewer.js)'
        );
    }

    $data[] = array(
        'module' => 'embed_images/viewer:dynadrive',
        'source' => "file(mods/embed_images/viewers/dynadrive/viewer.js)"
    );

    return $data;
}

function image_viewer_common()
{
    global $PHORUM;

    // Some settings overrides. For this to work, I had to take out
    // the createthumbBox() from the javascript library and put it here.
    $PHORUM["DATA"]["HEAD_TAGS"] .=

        # Make sure that the loading feedback divs are always hidden,
        # even if Phorum loads CSS after JavaScript.
        "<style type=\"text/css\">\n" .
        "#thumbBox {visibility:hidden;}\n" .
        "#thumbLoading {visibility:hidden;}\n" .
        "</style>\n" .

        "\n<script type=\"text/javascript\">\n" .
        "//<![CDATA[\n" .
        "/***********************************************************\n" .
        " * This site uses \"Image Thumbnail Viewer Script\"\n" .
        " * (c) Dynamic Drive (www.dynamicdrive.com)\n" .
        " * This notice must stay intact for legal use.\n" .
        " * Visit http://www.dynamicdrive.com/ for full source code\n" .
        " ***********************************************************/\n" .
        "thumbnailviewer['defineLoading'] = '<img src=\"{$PHORUM["http_path"]}/mods/embed_images/viewers/dynadrive/code/loading.gif\" alt=\"{$PHORUM['DATA']['LANG']['mod_embed_images']['LoadingImage']}\" />&nbsp;&nbsp;{$PHORUM["DATA"]["LANG"]["mod_embed_images"]["LoadingImage"]}';\n" .
        "thumbnailviewer['definefooter'] = '<div class=\"footerbar\">{$PHORUM["DATA"]["LANG"]["mod_embed_images"]["Close"]} X<\/div>';\n" .
        "thumbnailviewer['enableAnimation'] = " . (empty($PHORUM["mod_embed_images"]["dynadrive_animate"]) ? 'false' : 'true') . ";\n" .
        (empty($PHORUM["mod_embed_images"]["dynadrive_animate"]) ? "thumbnailviewer.opacitystring='';\n" : '') .
        (empty($PHORUM["mod_embed_images"]["dynadrive_noload"]) ? "thumbnailviewer.createthumbBox();\n" : '') .
        "//]]>\n" .
        "</script>\n";
}

?>
