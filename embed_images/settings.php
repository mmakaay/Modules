<?php

if (!defined("PHORUM_ADMIN")) return;

require_once("./mods/embed_images/defaults.php");

// Gather a list of all available viewers.
$image_viewers = array();
$dir = opendir("./mods/embed_images/viewers");
while ($entry = readdir($dir)) {
    if ($entry == '.' || $entry == '..') continue;
    $info = "./mods/embed_images/viewers/$entry/info.txt";
    if (file_exists($info)) {
        $fp = fopen($info, "r");
        if ($fp) {
            $description = trim(fgets($fp));
            fclose($fp);
            $image_viewers[$entry] = $description;
        }
    }
}

asort($image_viewers);

// save settings
if(count($_POST))
{
    $PHORUM["mod_embed_images"] = array(
        'max_width'     => (int)$_POST['max_width'],
        'max_height'    => (int)$_POST['max_height'],
        'handle_bbcode' => isset($_POST['handle_bbcode']) ? 1 : 0,
        'handle_url'    => isset($_POST['handle_url'])    ? 1 : 0,
        'handle_html'   => isset($_POST['handle_html'])   ? 1 : 0,
        'embed_html'    => isset($_POST['embed_html'])    ? 1 : 0,
        'embed_bbcode'  => isset($_POST['embed_bbcode'])  ? 1 : 0,
        'embed_url'     => isset($_POST['embed_url'])     ? 1 : 0,
        'embed_embatt'  => isset($_POST['embed_embatt'])  ? 1 : 0,
        'embed_allatt'  => isset($_POST['embed_allatt'])  ? 1 : 0,
        'debug'         => isset($_POST['debug'])         ? 1 : 0,
        'image_viewer'  => $_POST['image_viewer'],
        'cache_dir'     => trim($_POST['cache_dir']),
        'cache_url'     => trim($_POST['cache_url'])
    );

    if ($PHORUM['mod_embed_images']['cache_dir'] != '' &&
        !file_exists($PHORUM['mod_embed_images']['cache_dir'])) {
        phorum_admin_error("The cache directory can not be found.");
    }
    else {
        // Load the save scripts for the viewer specific settings.
        foreach ($image_viewers as $id => $name) {
            $file = "./mods/embed_images/viewers/$id/settings_save.php";
            if (file_exists($file)) {
                include($file);
            }
        }

        phorum_db_update_settings(array(
            "mod_embed_images" => $PHORUM["mod_embed_images"]
        ));

        phorum_admin_okmsg("The settings were successfully saved");
    }
}

include_once "./include/admin/PhorumInputForm.php";
$frm = new PhorumInputForm ("", "post", "Save");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "embed_images");

$frm->addbreak("Edit settings for the embed images module");

$row = $frm->addrow(
    "The maximum width for showing images inline (in pixels, 0 = unlimited)",
    $frm->text_box('max_width', $PHORUM["mod_embed_images"]["max_width"], 6)
);

$frm->addhelp($row,
    "Maximum width for displaying images inline",
    "When displaying images inline, large images might disturb your site's
     layout. If you want to limit the maximum width for showing images inline,
     then enter that width here (in pixels). If an image is shown scaled down,
     then users can still click on it to view the full scale image in a
     new window."
);

$row = $frm->addrow(
    "The maximum height for showing images inline (in pixels, 0 = unlimited)",
    $frm->text_box('max_height', $PHORUM["mod_embed_images"]["max_height"], 6)
);

$frm->addhelp($row,
    "Maximum height for displaying images inline",
    "When displaying images inline, large images might disturb your site's
     layout. If you want to limit the maximum height for showing images
     inline, then enter that height here (in pixels). If an image is shown
     scaled down, then users can still click on it to view the full scale
     image in a new window."
);

$row = $frm->addrow(
    "Show BBcode [img] tags as embedded images?",
    $frm->checkbox('embed_bbcode', 1, 'Yes',
    $PHORUM['mod_embed_images']['embed_bbcode'])
);
$frm->addhelp($row,
    "BBcode [img] tags",
    "If this option is enabled, then the module will recognize images that
     are placed in the body using a BBcode [img] tag.<br/>
     <br/>
     <strong>Note:</strong> The BBcode module itself does not need to be
     enabled for this option to work."
);
$row = $frm->addrow(
    "Show HTML &lt;img&gt; tags as embedded images?",
    $frm->checkbox('embed_html', 1, 'Yes',
    $PHORUM['mod_embed_images']['embed_html'])
);
$frm->addhelp($row,
    "HTML &lt;img&gt; tags",
    "If this option is enabled, then the module will recognize images that
     are placed in the body using an HTML &lt;img&gt; tag.<br/>
     <br/>
     <strong>Note:</strong> This option will only work if the HTML mod is
     enabled as well. The Phorum team does not recommend using the HTML mod,
     because wrong HTML can cause havoc on your page layout."
);

$row = $frm->addrow(
    "Show plain image URLs as embedded images?",
    $frm->checkbox('embed_url', 1, 'Yes',
    $PHORUM['mod_embed_images']['embed_url'])
);
$frm->addhelp($row,
    "Plain image URLs",
    "If this option is enabled, then the module will recognize plain URLs
     that are placed in the body and which point to a .gif .jpg or .png file."
);

$row = $frm->addrow(
    "Show embedded image attachments as embedded images?",
    $frm->checkbox('embed_embatt', 1, 'Yes',
    $PHORUM['mod_embed_images']['embed_embatt'])
);
$frm->addhelp($row,
    "Embedded image attachments",
    "If you have installed and enabled the Embed Attachments module as well,
     then users will be able to place links to attachments in line in the
     body. These links look like
     <nobr>\"[attachment &lt;file id&gt; &lt;description&gt;]\"</nobr><br/>
     With this option enabled, these links will be shown as embedded images
     in case the attachment contains a .gif .jpg or .png file.<br/>"
);

$row = $frm->addrow(
    "Show all image attachments as embedded images?",
    $frm->checkbox('embed_allatt', 1, 'Yes',
    $PHORUM['mod_embed_images']['embed_allatt'])
);
$frm->addhelp($row,
    "All image attachments",
    "If this option is enabled, then all .gif .jpg or .png attachments will
     be shown as embedded images, even if they are not explicitly placed in
     the body by the message author."
);

$row = $frm->addrow(
    "What image viewer do you want to use for displaying full size images?",
    $frm->select_tag(
        "image_viewer",
        $image_viewers, $PHORUM["mod_embed_images"]["image_viewer"],
        'id="image_viewer" onchange="toggleImageViewer()"'
    )
);

$frm->addhelp($row,
    "Type of image viewer",
    "Here you can choose what type of image viewer you want to use for
     displaying full size images (in case a visitor clicks on an image
     in the message). The option \"Browser\" will open a new browser window
     to show the image. The other options will display the image in a
     fancy way within the active window.<br/>
     <br/>
     The preferred fancy displaying method is \"jQuery FancyBox\", because
     it makes use of the jQuery javascript library that is bundled with
     Phorum and because the viewer provides some good features (like
     automatically scaling to fit the browser window).<br/>
     <br/>
     You might want to run \"Lightbox\" if you are already using lightbox,
     prototypejs and/or scriptaculous on your site. You might want to
     run \"Slimbox\" if you are already using slimbox or mootools on
     your site.<br/>
     <br/>
     If unsure what to use, then simply configure the viewers one by one
     and test them out to see which one best fits your needs."
);

// Build viewer specific settings screens.
$viewer_settings = '';
foreach ($image_viewers as $id => $name)
{
    if ($id == '.' || $id == '..') continue;
    $viewer_settings .=
        '<div style="display:none" id="image_viewer_options_'.$id.'">';

    // Load the settings script for the viewer.
    $file = "./mods/embed_images/viewers/$id/settings.php";
    if (file_exists($file)) {
        ob_start();
        include($file);
        $viewer_settings .= ob_get_contents();
        ob_end_clean();
    }

    $viewer_settings .= '</div>';
}
$frm->addmessage($viewer_settings);

$frm->addbreak("Advanced caching options. See help info for security notices.");

$row = $frm->addrow(
    "Cache directory for scaled images (empty = use standard Phorum cache)",
    $frm->text_box('cache_dir', $PHORUM['mod_embed_images']['cache_dir'], 30)
);
$frm->addhelp($row,
    "Cache directory",
    "By default, this module will use the Phorum cache for caching scaled down
     images and their data. This cache won't be permanent, but for scaled down
     images, it makes perfect sense to have a somewhat permanent cache (since
     scaling is pretty expensive server operation).<br/>
     <br/>
     Using this option, you can specify a directory that this module can use
     for storing scaled images. Note that this directory should be writable
     for the webserver. It can be provided as an absolute path or a path
     relative to the Phorum install dir.<br/>
     <br/>
     <strong>Security notice</strong>: If your installation contains a closed
     forum for which the image attachments should be kept private, you should
     make sure that the cache directory is either outside the document root or
     that the webserver is configured to disallow direct browser access to the
     files"
);

$row = $frm->addrow(
    "URL for scaled and cached images (empty = don't use feature)",
    $frm->text_box('cache_url', $PHORUM['mod_embed_images']['cache_url'], 30)
);
$frm->addhelp($row,
    "Cache URL",
    "If you have provided a cache directory in the previous option, then
     already scaled images will be served from that cache. This is done
     through a PHP script. If the cache directory can be accessed at a
     certain URL, then you can enter that URL here. If a URL is
     configured, Phorum can load the scaled images directly through the
     direct URL, which improves the performance by saving on PHP scripts
     that have to run.<br/>
     <br/>
     <strong>Security notice</strong>: If your installation contains a closed
     forum for which the image attachments should be kept private, you should
     not make use of this option. In that case, the cache directory should
     also be inaccessible from the web."
);

$frm->addmessage("");

$frm->addbreak("Debugging problems");

include './include/api/http_get.php';
$row = $frm->addrow(
    "Platform support for the http_get API layer",
    phorum_api_http_get_supported() ? 'OK' : 'NOT OK'
);
$frm->addhelp($row,
    "http_get platform support",
    "The hosting platform must support downloading files via HTTP.
     This module uses this feature to download images directly
     to the server, where they can be inspected and scaled down
     when needed. The hosting platform must support one of the
     following features to make this work:<br/>
     <ul>
       <li>The \"curl\" PHP module must be loaded or</li>
       <li>The \"sockets\" PHP module must be loaded or</li>
       <li>The PHP setting \"allow_url_fopen\" must be enabled</li>
     </ul>
     Please contact your hosting provider if this check returns \"NOT OK\"."

);

$page = phorum_api_http_get("http://www.google.com");
$row = $frm->addrow(
    "Download test (tries to load http://www.google.com)",
    $page && strstr($page, '<html>') ? 'OK' : 'NOT OK'
);
$frm->addhelp($row,
    "Download test",
    "This check tries to download the Google homepage via the
     http_get API. If the platform support check returns \"OK\",
     but this check returns \"NOT OK\", then the hosting platform
     might be blocking outgoing HTTP connections from the
     webserver.<br/>
     <br/>
     Please contact your hosting provider if this check returns \"NOT OK\"."
);


include './include/api/image.php';
$method = phorum_api_image_supported();
$row = $frm->addrow(
    "Platform support for the image API layer",
    $method ? "OK (using method \"$method\")" : "NOT OK"
);
$frm->addhelp($row,
    "image scaling platform support",
    "The hosting platform must support image scaling.
     This module uses this feature to downscale images when needed.
     The hosting platform must support one of the following features
     to make this work:<br/>
     <ul>
       <li>The \"gd\" PHP module must be loaded or</li>
       <li>The \"imagick\" PHP module must be loaded or</li>
       <li>The ImageMagick \"convert\" application must be installed</li>
     </ul>
     Please contact your hosting provider if this check returns \"NOT OK\"."
);

$row = $frm->addrow(
    "Enable debugging for this module",
    $frm->checkbox('debug', 1, 'Yes', $PHORUM['mod_embed_images']['debug'])
);
$frm->addhelp($row,
    "Enable debugging for this module",
    "When this module fails to embed images in messages, then this option
     can be enabled to gather debugging information. This debug information
     will be shown directly in the Phorum front end when reading messages."
);

$frm->addmessage("");
$frm->show();
?>

<script type="text/javascript">
function toggleImageViewer()
{
    var s = document.getElementById('image_viewer');
    if (!s) return;
    var sel = s.selectedIndex;
    var item = s.options[sel].value; <?php

    foreach ($image_viewers as $id => $name) {
        print "    var opts_$id = document.getElementById('image_viewer_options_$id');\n";
        print "    if (opts_$id) opts_$id.style.display = " .
              "(item == '$id') ? 'block' : 'none';\n";
    }
    ?>
}

toggleImageViewer();
</script>
