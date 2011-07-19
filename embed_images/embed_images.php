<?php

if (!defined("PHORUM")) return;

// ----------------------------------------------------------------------
// INIT
// ----------------------------------------------------------------------

require_once("./mods/embed_images/defaults.php");

// Load the PHP functions that are required for the configured viewer
function phorum_mod_embed_images_common()
{
    $viewer = basename($GLOBALS['PHORUM']['mod_embed_images']['image_viewer']);
    if (file_exists("mods/embed_images/viewers/$viewer/functions.php")) {
        require_once("mods/embed_images/viewers/$viewer/functions.php");
    }

    // Allow viewers to run code in the common hook (e.g. for adding
    // data to the {HEAD_TAGS} template variable).
    if (function_exists('image_viewer_common')) {
        image_viewer_common();
    }
}

// Allow viewers to hook into after_header.
function phorum_mod_embed_images_after_header()
{
    if (function_exists('image_viewer_after_header')) {
        image_viewer_after_header();
    }
}

// Load JavaScript code for this module and the configured viewer.
function phorum_mod_embed_images_javascript_register($data)
{
    // Static JavaScript for this module.
    $data[] = array(
        'module' => 'embed_images',
        'source' => 'file(mods/embed_images/embed_images.js)'
    );

    // Allow viewers to run code in the javascript_register hook
    // (for loading extra JavaScript code for that viewer).
    if (function_exists('image_viewer_javascript_register')) {
        $data = image_viewer_javascript_register($data);
    }

    return $data;
}


// Load CSS code for this module and the configured viewer.
function phorum_mod_embed_images_css_register($data)
{
    // Load the CSS code for this module.
    $data['register'][] = array(
        'module' => 'embed_images',
        'where'  => 'after',
        'source' => 'template(embed_images::css)'
    );

    // Allow viewers to run code in the css_register hook
    // (for loading extra CSS code for that viewer).
    if (function_exists('image_viewer_css_register')) {
        $data = image_viewer_css_register($data);
    }

    return $data;
}


// ----------------------------------------------------------------------
// MESSAGE FORMATTING
// ----------------------------------------------------------------------

// Phase 1 formatting for [img] and <img> images: replace these with
// "[embed_images <key>]" tags.
function phorum_mod_embed_images_format($data)
{
    global $PHORUM;

    // Regular expressions for finding BBocde [url] declarations around
    // the images. We need to check for these so we can let the software
    // decide whether to show an image viewer or to follow a link when the
    // user clicks an image thumbnail.
    $url_pre = '\[url=((?:http|https|ftp|mailto):\/\/[a-z0-9;\/\?:@=\&\$\-_\.\+!*\'\(\),~%# ]+?)\]';
    $url_post = '\[\/url\]';

    if (!isset($PHORUM['mod_embed_images']['format_fixup'])) {
        $PHORUM['mod_embed_images']['format_fixup'] = array();
    }

    // Run formatting on the message bodies.
    foreach($data as $id => $message)
    {
        // Skip formatting if there is no body to format.
        if (!isset($message["body"])) continue;

        // Preprocess images to a normalized format.
        // This normalized format will be replaced with an embedded
        // image viewer in the format_fixup hook.

        // Process HTML <img> tags.
        // Unfortunately this does not catch all images. Users can post
        // somewhat mangled <img> tags that are not handled by this module
        // but which will result in a shown image because the HTM module
        // allows the HTML and most browsers will render the mangled HTML.
        // Extended parsing would be required do handle this correctly.
        // Let's not waste that CPU time. Better disable the evil HTML
        // module :-]
        if (!empty($PHORUM['mod_embed_images']['embed_html']) &&
            preg_match_all('!<\s*img\s+[^>]*?src\s*=\s*"?([^">]+)"?\s*[^>]*/?>!i', $data[$id]['body'], $m))
        {
            foreach ($m[0] as $mid => $match)
            {
                $title = '';

                // Check for a title or alt property in the image.
                if (preg_match_all('!(title|alt)\s*=\s*"([^"]+)"!i',$match,$t)){
                    foreach ($t[0] as $tid => $tmatch) {
                        $title = htmlspecialchars($t[2][$tid]);
                        if (strtolower($t[1][$tid] == 'title')) break;
                    }
                // Use the image name as the title.
                } else {
                    $name = htmlspecialchars(urldecode(basename($m[1][$mid])));
                    $name = preg_replace('/\?.*$/', '', $name);
                    $ext = strtolower(preg_replace('/^.*\./', '', $name));
                    if ($ext == 'jpg' || $ext == 'jpeg' ||
                        $ext == 'gif' || $ext == 'png') {
                        $title = $name;
                    }
                }

                $url = $m[1][$mid];
                $key = md5($title.'|'.$url);
                $tag = "[embed_image $key]";
                $data[$id]['body'] =
                    str_replace($match, $tag, $data[$id]['body']);
                $PHORUM['mod_embed_images']['format_fixup'][$id][$key] =
                    array($tag, 'url', $url, $title);
            }
        }

        // Process bare image URLs.
        if (!empty($PHORUM['mod_embed_images']['embed_url']) &&
            preg_match_all("!(?:[^='\"\]]|^)(https?://[a-z0-9;/\?:@=\&\$\-_\.\+\!*'\(\),~%#]+)!is", $data[$id]['body'], $m))
        {
            foreach ($m[0] as $mid => $match)
            {
                // Try to determine the image filename. If we cannot find
                // an image file name (ending in .jp(e)g, .gif or .png), we
                // skip the URL.
                $name = basename($m[1][$mid]);
                $name = preg_replace('/\?.*$/', '', $name);
                $ext = strtolower(preg_replace('/^.*\./', '', $name));
                if ($ext !== 'jpg' && $ext != 'jpeg' &&
                    $ext !== 'gif' && $ext !== 'png') {
                    continue;
                }

                $url   = $m[1][$mid];
                $key   = md5($name.'|'.$url);
                $tag   = "[embed_image $key]";
                $data[$id]['body'] =
                    str_replace($match, $tag, $data[$id]['body']);
                $PHORUM['mod_embed_images']['format_fixup'][$id][$key] =
                    array($tag, 'url', $url, $name);
            }
        }

        // Handle automatic displaying of image attachments as embedded
        // images in the message.
        $has_images = FALSE;
        if (!empty($PHORUM['mod_embed_images']['embed_allatt']) &&
            !empty($data[$id]['attachments'])) {

            foreach ($data[$id]['attachments'] as $aid => $attachment)
            {
                // Make sure that the data array $id is used by the embedded
                // attachment function to setup the data for the fixup hook.
                $message = $data[$id];
                $message['message_id'] = $id;

                $attach = phorum_mod_embed_images_render_embedded_attachment(
                    $attachment, $message
                );
                if (isset($attach['rendered_raw'])) {
                    if (!$has_images) {
                        $data[$id]['body'] .=
                            '<br style="clear:both"/>' .
                            '<div class="mod_embed_images_attachments">';
                        $has_images = TRUE;
                    }
                    $data[$id]['body'] .= $attach['rendered_raw'];
                    unset($data[$id]['attachments'][$aid]);
                }
            }
        }
        if ($has_images) $data[$id]['body'] .= '</div><br style="clear:both"/>';
    }

    return $data;
}


// Phase 1 formatting for the embedded attachments module.
// This will replace embedded image attachments with
// "[embed_images <key>]" tags.
//
// This hook is called from the embed_attachments module.
// The function is also used for implementing the handling for "embed_allatt"
// from the phorum_mod_embed_images_format() function.
//
function phorum_mod_embed_images_render_embedded_attachment(
    $attachment, $message)
{
    global $PHORUM;

    if (!isset($PHORUM['mod_embed_images']['format_fixup'])) {
        $PHORUM['mod_embed_images']['format_fixup'] = array();
    }

    // Check if the attachment is a supported image file.
    if (!preg_match('/\.(gif|jpe?g|png)$/i', $attachment['name'])) {
        return $attachment;
    }

    // Inherit the attachment filename as the description if no description
    // is available in the attachment already.
    if (!isset($attachment['description'])) {
        $attachment['description'] = $attachment['name'];
    }

    $key = md5($attachment['description'].'|'.$attachment['file_id']);
    $tag = "[embed_image $key]";
    $attachment['rendered_raw'] = $tag;
    $PHORUM['mod_embed_images']['format_fixup'][$message['message_id']][$key] =
       array($tag, 'file', $attachment['file_id'], $attachment['description']);

    return $attachment;
}

// If [img] handling is enabled in the settings, then register the required
// BBcode hooks for this.
function phorum_mod_embed_images_bbcode_register($tags)
{
    global $PHORUM;
    if (empty($PHORUM['mod_embed_images']['embed_bbcode'])) return $tags;

    $tags['img'] = array(
        BBCODE_INFO_DESCRIPTION   =>'[img]http://example.com/image.jpg[/img]',
        BBCODE_INFO_HASEDITORTOOL => TRUE,
        BBCODE_INFO_DEFAULTSTATE  => 2,
        BBCODE_INFO_ARGS          => array('img' => '', 'size' => ''),
        BBCODE_INFO_CALLBACK      => 'embed_images_bbcode_img_handler'
    );

    $tags['url'] = array(
        BBCODE_INFO_DESCRIPTION   =>
            '[url=http://example.com]cool site![/url]<br/>' .
            '[url]http://example.com[/url]<br/>' .
            'For adding website links.',
        BBCODE_INFO_HASEDITORTOOL => TRUE,
        BBCODE_INFO_DEFAULTSTATE  => 2,
        BBCODE_INFO_ARGS          => array('url' => ''),
        BBCODE_INFO_CALLBACK      => 'embed_images_bbcode_url_handler'
    );

    return $tags;
}

function embed_images_bbcode_img_handler($content, $args, &$message)
{
    global $PHORUM;

    if ($args['img'] == '') {
        if (strpos($content, '<') !== FALSE ||
            strpos($content, '"') !== FALSE ||
            strpos($content, '>') !== FALSE)
            $content = preg_replace('/[<">].*[<">]/', '', $content);
        $args['img'] = $content;
    }

    // Might happen with incorrectly nested image tags. Let's try to
    // prevent broken images by taking the inner tag and skipping
    // outer tag(s).
    if (preg_match('!^.*(\[embed_image \w+\]).*$!', $content, $m)) {
        return $m[1];
    }

    // Add protocol if missing in the image URL.
    if (!preg_match('!^\w+://!', $args['img'])) {
        $args['img'] = 'http://'.$args['img'];
    }

    // Try to determine the image filename. If we cannot find
    // a reliable name (ending in .jp(e)g, .gif or .png), we
    // fallback to an empty string.
    $name = basename($args['img']);
    $name = preg_replace('/\?.*$/', '', $name);
    $ext = strtolower(preg_replace('/^.*\./', '', $name));
    if ($ext != 'jpg' && $ext != 'jpeg' &&
        $ext != 'gif' && $ext != 'png') {
        $name = '';
    }

    $url   = $args['img'];
    $key   = md5($name.'|'.$url);
    $tag   = "[embed_image $key]";

    $PHORUM['mod_embed_images']['format_fixup'][$message['message_id']][$key] =
        array($tag, 'url', $url, $name);

    return $tag;
}

// Catch [url] tags around images. These tags are stripped from the
// body code and a new format_fixup key is generated, including the
// target URL as the fifth element in the info array.
function embed_images_bbcode_url_handler($content, $args, &$message)
{
    global $PHORUM;

    if (preg_match("/\[embed_image (\w+)\]/", $content, $m))
    {
        $url = $args['url'];
        $key = $m[1];
        $id  = $message['message_id'];

        if (!isset($PHORUM['mod_embed_images']['format_fixup'][$id][$key])) {
            return bbcode_url_handler($content, $args, $message);
        }

        $info = $PHORUM['mod_embed_images']['format_fixup'][$id][$key];
        $newkey = md5($key . $url);
        $newtag = "[embed_image $newkey]";
        $info[0] = $newtag;
        $info[4] = $url;

        // For FTP and HTTP(s) connections, we use the host name
        // that is being linked to as the link description.
        if (preg_match('!^(?:ftp|http|https)://([\w\.]+)!i', $url, $u))
        {
            $info[3] = $u[1];
        }

        $PHORUM['mod_embed_images']['format_fixup'][$id][$newkey] = $info;

        return $newtag;
    }

    return bbcode_url_handler($content, $args, $message);
}


// Phase 2 formatting: replace all "[embed_images <key>]" links with
// embedded image viewers. Also make sure that a <br/> is placed
// after the body, if an embedded image is the last item in the body.
// That is needed to not let the floating image divs go outside the
// message body layout (the <br/> should have style clear:both for this
// to work).
function phorum_mod_embed_images_format_fixup($messages)
{
    global $PHORUM;
    static $viewer_id = 0;

    // Return immediately if we didn't to any replacements in the format hook.
    if (empty($PHORUM['mod_embed_images']['format_fixup'])) return $messages;

    $requested_size =
        (empty($PHORUM['mod_embed_images']['max_width'])
         ? 'NULL' : $PHORUM['mod_embed_images']['max_width']) .  'x' .
        (empty($PHORUM['mod_embed_images']['max_height'])
         ? 'NULL' : $PHORUM['mod_embed_images']['max_height']);

    foreach($PHORUM['mod_embed_images']['format_fixup'] as $id => $fixes)
    {
        // Check if we really have the message in our messages array.
        if (!isset($messages[$id]['message_id'])) continue;

        // Make sure that the body has a break after the last image.
        if (preg_match('!.*<(?:br ?/?|phorum break)>(.*)$!', $messages[$id]['body'], $m)) {
            if (preg_match('!\[embed_image [^\]]+\]!', $m[1])) {
                $messages[$id]['body'] .= '<br/>';
            }
        } else {
            $messages[$id]['body'] .= '<br/>';
        }

        // Apply final replacement for images.
        foreach ($fixes as $fix)
        {
            list ($tag, $type, $image, $title) = $fix;
            $target_url = isset($fix[4]) ? $fix[4] : NULL;

            // Generate full image and thumbnail image URLs.
            if ($type == 'url') {
                $cache_key = md5("url:$image");
                $url = htmlspecialchars($image);
                $thumbnail_url = phorum_get_url(
                    PHORUM_ADDON_URL,
                    'module=embed_images',
                    'url='.urlencode($image)
                );
                $ajax_url = phorum_get_url(
                    PHORUM_ADDON_URL,
                    'module=embed_images',
                    'check_scaling=1',
                    'url='.urlencode($image)
                );
            } elseif ($type == 'file') {
                $cache_key = md5("attachment:".(int)$image);
                $url = phorum_get_url(
                    PHORUM_FILE_URL,
                    'file='.(int)$image
                );
                $thumbnail_url = phorum_get_url(
                    PHORUM_ADDON_URL,
                    'module=embed_images',
                    'file_id='.(int)$image
                );
                $ajax_url = phorum_get_url(
                    PHORUM_ADDON_URL,
                    'module=embed_images',
                    'check_scaling=1',
                    'file_id='.(int)$image
                );
            } else trigger_error(
                'Internal error in embed_images module: Illegal image fixup ' .
                'type: ' . $type
            );

            // Check if we already have a thumbnail in the cache.
            // If we do, then we do not use the multi stage Ajax request
            // system, but directly show the image or error.
            // When in debugging mode, we will not check the cache.
            $cache = empty($PHORUM['mod_embed_images']['debug'])
                   ? mod_embed_images_cache_get($cache_key, $requested_size)
                   : NULL;
            $error = NULL;
            $is_cached = FALSE;
            $is_scaled = FALSE;
            $w = NULL; $h = NULL;
            if (!empty($cache)) {
                if (!is_array($cache)) {
                    $error = $cache;
                    $thumbnail_url = NULL;
                } else {
                    if (!empty($cache['image_web'])) {
                        $thumbnail_url = $cache['image_web'];
                    }
                    $is_scaled = $cache['size'] != $cache['scaled_size'];
                    list($w,$h) = explode('x', $cache['scaled_size']);
                }
                $is_cached = TRUE;
            }

            // Setup template data.
            $PHORUM['DATA']['VIEWER'] = array(
                'URL'            => $url,
                'THUMBNAIL_URL'  => $thumbnail_url,
                'AJAX_URL'       => $ajax_url,
                'TARGET_URL'     => htmlspecialchars($target_url),
                'DESCRIPTION'    => $title,
                'MESSAGE_ID'     => $messages[$id]['message_id'],
                'MAX_W'          => $PHORUM['mod_embed_images']['max_width'],
                'MAX_H'          => $PHORUM['mod_embed_images']['max_height'],
                'W'              => $w,
                'H'              => $h,
                'ERROR'          => $error,
                'IS_CACHED'      => $is_cached,
                'IS_SCALED'      => $is_scaled
            );

            $qtag = preg_quote($tag, '/');

            while (strstr($messages[$id]['body'], $tag))
            {
                // Formatting called from the search page? Then we fall back
                // to a realy simple formatting. On that page, we are showing
                // an excerpt from the search results, so we're not interested
                // in the image there. We do want to keep all the code from
                // the excerpt though.
                if (phorum_page == 'search') {
                    $viewer = '';
                }
                // For other pages, we use the viewer formatting template.
                else
                {
                    // Each viewer on the page needs a unique id. We cannot
                    // only use a simple counter for this, because (partial)
                    // messages might be rendered from ajax requests. That
                    // should not result in duplicate ids. This id generation
                    // should give us unique ids.
                    $unique_id = md5(
                        $viewer_id++ . microtime() .$cache_key.$requested_size
                    );
                    $PHORUM['DATA']['VIEWER']['ID'] = $unique_id;

                    // Load the image viewer template.
                    ob_start();
                    include(phorum_get_template('embed_images::thumbnail'));
                    $viewer = ob_get_contents();
                    ob_end_clean();
                }

                // We have to use preg_replace here, so we can do one
                // replacement at a time. With str_replace, all occurrances
                // would be replaced and we would not have an option to
                // assign different ids to the image viewers.
                $messages[$id]['body'] = preg_replace(
                    "/$qtag/", $viewer, $messages[$id]['body'], 1);
            }
        }
    }

    $PHORUM['mod_embed_images']['format_fixup'] = array();

    return $messages;
}

// ----------------------------------------------------------------------
// CACHE HANDLING
// ----------------------------------------------------------------------

/**
 * Build the path in which the data for a certain cache key is stored.
 *
 * @param string $key
 *     The cache key that identifies the source image.
 *
 * @param string $requested_size
 *     The representation of the required thumbnail size.
 *
 * @return array
 *     An array containing two elements. The first element is the filesystem
 *     path for the thumbnail cache. The second one is NULL or an HTTP URL
 *     pointing to the web equivalent of the first return value.
 */
function mod_embed_images_build_cache_dir($key, $requested_size)
{
    $path = $GLOBALS['PHORUM']['mod_embed_images']['cache_dir'] .
            DIRECTORY_SEPARATOR . wordwrap($key, 11, DIRECTORY_SEPARATOR, 1) .
            '_' . $requested_size;

    $dir = dirname($path);

    $webpath = $GLOBALS['PHORUM']['mod_embed_images']['cache_url']
             ? $GLOBALS['PHORUM']['mod_embed_images']['cache_url'] .
               '/' . wordwrap($key, 11, '/', 1) . '_' . $requested_size
             : NULL;

    return array($path, $webpath);
}

/**
 * Recursive mkdir for a given path.
 *
 * @param string $path
 *     The path to create.
 */
function mod_embed_images_mkdir($path)
{
    @ini_set('track_errors', TRUE);
    if (empty($path)) return FALSE;
    if (is_dir($path)) return TRUE;
    if (!mod_embed_images_mkdir(dirname($path))) return FALSE;
    if (@mkdir($path) === FALSE) {
        die("mkdir($path) failed:<br/>" . strip_tags($php_errormsg));
    }
    return TRUE;
}

/**
 * This function can be used to retrieve data from the embed images cache.
 *
 * @param string $key
 *     The cache key that identifies the source image.
 *
 * @param string $requested_size
 *     The representation of the required thumbnail size.
 *
 * @return mixed
 *     If no cache entry is available, then NULL is returned.
 *     For cached errors, a string is returned.
 *     For successful caches, an array containing cached info is returned.
 */
function mod_embed_images_cache_get($key, $requested_size)
{
    $cache = NULL;

    // Check for a cache entry in our permanent file cache if cache_dir is set.
    if ($GLOBALS['PHORUM']['mod_embed_images']['cache_dir'])
    {
        list($path,$webpath) =
            mod_embed_images_build_cache_dir($key, $requested_size);

        if (file_exists("$path.php"))
        {
            $cache = @unserialize(file_get_contents("$path.php"));
            if (!is_array($cache))
            {
                // Data returned from cache could not be unserialized.
                // Unlink the cache file to clear the cache item.
                @unlink("$path.php");
                $cache = NULL;
            }
            else
            {
                $image_file = $path . $cache['image_extension'];
                if (!file_exists($image_file))
                {
                    // Image file could not be found in the cache.
                    // Unlink the cache file to clear the cache item.
                    @unlink("$path.php");
                    $cache = NULL;
                }
                else
                {
                    // Successfully retrieved the cache data. The fields
                    // "image_file" and "image_web" are added dynamically to
                    // the cache data array.

                    $cache['image_file'] = $image_file;

                    $image_web = $webpath === NULL
                               ? NULL
                               : $webpath . $cache['image_extension'];
                    $cache['image_web'] = $image_web;
                }
            }
        }
    }

    // No file cached item found? Then check the Phorum cache.
    if ($cache === NULL)
    {
        $key = $key . "_" . $requested_size;
        $cache = phorum_cache_get('embed_images', $key);
        if (is_array($cache) && !empty($cache['image'])) {
            $cache['image'] = base64_decode($cache['image']);
        }
    }

    return $cache;
}

/**
 * Store a cache entry.
 *
 * @param string $key
 *     The cache key that identifies the source image.
 *
 * @param string $requested_size
 *     The representation of the required thumbnail size.
 *
 * @param array $val
 *     The data to cache.
 */
function mod_embed_images_cache_put($key, $requested_size, $val)
{
    // Use our own permanent file cache if a cache_dir is set. Do
    // not cache errors using this system. We cache those using the
    // standard Phorum cache, so we can assign a TTL to them.
    if ($GLOBALS['PHORUM']['mod_embed_images']['cache_dir'] && is_array($val))
    {
        require_once('./include/api/write_file.php');
        list($path, $webpath) =
            mod_embed_images_build_cache_dir($key, $requested_size);

        // Find a good extension for the image cache file.
            if ($val['mime'] == 'image/png')  $ext = '.png';
        elseif ($val['mime'] == 'image/jpeg') $ext = '.jpg';
        elseif ($val['mime'] == 'image/jpg')  $ext = '.jpg';
        elseif ($val['mime'] == 'image/gif')  $ext = '.gif';
        else die("mod_embed_images_cache_put(): " .
                 "MIME type $val[mime] is not handled");

        // Create the file path if it does not yet exist.
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mod_embed_images_mkdir($dir);
        }

        // Store image to disk.
        if (!phorum_api_write_file("$path$ext", $val['image'])){
            die("mod_embed_images_cache_put(): " . phorum_api_strerror());
        }

        // Store data to disk.
        unset($val['image']);
        $val['image_extension'] = $ext;
        if (!phorum_api_write_file("$path.php", serialize($val))) {
            die("mod_embed_images_cache_put(): " . phorum_api_strerror());
        }
    }
    // Use the standard Phorum cache.
    else
    {
        $key = $key . '_' . $requested_size;

        if (is_array($val) && !empty($val['image'])) {
            $val['image'] = base64_encode($val['image']);
        }

        // Cache errors less long (5 minutes), so temporary failures
        // can resolve themselves after a short while.
        $ttl = is_array($val) ? PHORUM_CACHE_DEFAULT_TTL : 300;

        phorum_cache_put('embed_images', $key, $val, $ttl);
    }
}

// ----------------------------------------------------------------------
// AJAX AND IMAGE DELIVERY ADDON
// ----------------------------------------------------------------------

// The addon can be called in two ways:
//
// * check if the image is available and scaling can be done
//   This is done by providing a "check_scaling" parameter with the
//   other standard arguments. In this case, the code will check
//   if a cache entry is available for the image and it will
//   return either
//
//     "OK <orig width>x<orig height> <scaled width>x<scaled height>"
//
//   if a valid thumbnail is available or an error string in case there
//   was some problem in retrieving or scaling the image. The nice way
//   of doing this check first, is that we can provide loading and error
//   feedback in the front end.
//
// * return the scaled image data. Possibly, if we have configured a cache
//   directory and a URL pointing to the cache directory, This call can
//   return a Location: header, pointing to the URL of the scaled image.
//
function phorum_mod_embed_images_addon()
{
    $PHORUM    = $GLOBALS['PHORUM'];

    $image     = NULL;
    $scaled    = NULL;
    $size      = NULL;
    $scsize    = NULL;
    $mime      = NULL;

    $do_debug  = !empty($PHORUM['mod_embed_images']['debug']);
    $debug     = '<b>Debug info:</b><br/>';

    $requested_size =
        (empty($PHORUM['mod_embed_images']['max_width'])
         ? 'NULL' : $PHORUM['mod_embed_images']['max_width']) .  'x' .
        (empty($PHORUM['mod_embed_images']['max_height'])
         ? 'NULL' : $PHORUM['mod_embed_images']['max_height']);

    // "file_id" parameter is set. This is an attachment.
    if (isset($PHORUM['args']['file_id']))
    {
        $file_id = $PHORUM['args']['file_id'];
        $cache_key = md5("attachment:$file_id");
        $debug .= "Load local file: $file_id<br/>";

        // Check if the user has read access for the attachment.
        if (file_exists('./include/api/file_storage.php')) {
            require_once('./include/api/file_storage.php');
        } else {
            require_once('./include/api/file.php');
        }
        $file_id = (int) $PHORUM['args']['file_id'];
        $file = phorum_api_file_check_read_access($file_id);
        if (!$file) die(phorum_api_strerror());
    }
    // "url" parameter is set. This is a URL pointing to an image.
    elseif (isset($PHORUM['args']['url']))
    {
        $url = $PHORUM['args']['url'];
        $debug .= "Load file from URL: $url<br/>";
        $cache_key = md5("url:$url");
    }
    // Invalid Ajax request done.
    else die("Missing argument (one of \"file_id\" or \"url\")");

    // Try to load an image from the cache, unless we're in debug mode.
    $cache = $do_debug
           ? NULL
           : mod_embed_images_cache_get($cache_key, $requested_size);

    // Cache returned an error message.
    if ($cache !== NULL && !is_array($cache)) {
        if ($do_debug) {
            $debug .= "Cache error: $cache<br/>";    
            die($debug);
        } else {
            die($cache);
        }
    }

    // No cached scaled image found. We need to generate one ourselves now.
    if (empty($cache))
    {
        // Retrieve the source image file.
        if (isset($PHORUM['args']['file_id']))
        {
            $debug .= "Load local image file<br/>";

            $image = phorum_api_file_retrieve($file);
            // No cache for errors here. Problems here might be user
            // permission related.
            if (!$image) {
                if ($do_debug) {
                    $debug .= "Error loading file: " . phorum_api_strerror();
                    die($debug);
                } else {
                    die(phorum_api_strerror());
                }
            }
            $image = $image['file_data'];
        }
        elseif (isset($PHORUM['args']['url']))
        {
            $debug .= "Load remote image file<br/>";
            $url = $PHORUM['args']['url'];

            include_once("./include/api/http_get.php");
            $image = phorum_api_http_get($url);
            if (!$image) {
                if ($do_debug) {
                    $debug .= "Error loading file: " . phorum_api_strerror();
                    die($debug);
                } else {
                    $error = phorum_api_strerror();
                    mod_embed_images_cache_put(
                        $cache_key, $requested_size, $error
                    );
                    die($error);
                }
            }
        }

        $max_w = $PHORUM['mod_embed_images']['max_width'];
        $max_h = $PHORUM['mod_embed_images']['max_height'];
        $debug .= "Create thumbnail using {$max_w}x{$max_h} boundary<br/>";

        // Create a thumbnail for the image.
        include_once("./include/api/image.php");
        $thumb = phorum_api_image_thumbnail(
            $image,
            $PHORUM['mod_embed_images']['max_width'],
            $PHORUM['mod_embed_images']['max_height']
        );

        // An error was returned by the thumbnail code. Cache the error.
        if (!$thumb)
        {
            if ($do_debug) {
                $debug .= "Creating thumbnail failed: " . phorum_api_strerror();
                die($debug);
            } else {
                $error = phorum_api_strerror();
                mod_embed_images_cache_put($cache_key, $requested_size, $error);
                die($error);
            }
        }
        // The image was successfully scaled down. Cache the scaled image,
        // unless we are running in debug mode.
        else
        {
            $scaled = $thumb['image'] === NULL ? $image : $thumb['image'];

            $debug .= "Original image was " .
                      $thumb['cur_w'].'x'.$thumb['cur_h'] . "<br/>";
            $debug .= "Thumbnail created at " .
                      $thumb['new_w'].'x'.$thumb['new_h'] . "<br/>";
            $debug .= "Thumbnail MIME type: {$thumb['new_mime']}<br/>";

            $cache = array(
                'mime'           => $thumb['new_mime'],
                'image'          => $scaled,
                'requested_size' => $requested_size,
                'size'           => $thumb['cur_w'].'x'.$thumb['cur_h'],
                'scaled_size'    => $thumb['new_w'].'x'.$thumb['new_h']
            );

            if (!$do_debug) {
                mod_embed_images_cache_put($cache_key, $requested_size, $cache);
            }
        }
    }

    // If the "check_scaling" parameter is provided, then this
    // is an ajax request for checking the scaling. We can return
    // a response now.
    if (!empty($PHORUM['args']['check_scaling'])) {
        if ($do_debug) print $debug;
        print 'OK ' . $cache['size'] . ' ' . $cache['scaled_size'];
        exit;
    }

    // This is a request for image data.
    // If we have a web URL for the scaled down image, then we can
    // redirect to that one here.
    if (!empty($cache['image_web'])) {
        phorum_redirect_by_url($cache['image_web']);
    }
    // Without a web URL, provide the images through this script directly.
    else
    {
        // Load the image data from our permanent cache if needed.
        if (empty($cache['image']) && isset($cache['image_file'])) {
            $cache['image'] = @file_get_contents($cache['image_file']);
        }

        // Avoid using any output compression or handling on the sent data.
        @ini_set("zlib.output_compression", "0");
        @ini_set("output_handler", "");

        // Get rid of any buffered output so far.
        phorum_ob_clean();
        header('Content-Type: ' . $cache['mime']);
        print $cache['image'];
    }

    exit(0);
}

?>
