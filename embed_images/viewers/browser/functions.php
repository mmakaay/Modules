<?php

function image_viewer_javascript_register($data)
{
    $data[] = array(
        'module' => 'embed_images/viewer:browser',
        'source' => "file(mods/embed_images/viewers/browser/viewer.js)"
    );

    return $data;
}

?>
