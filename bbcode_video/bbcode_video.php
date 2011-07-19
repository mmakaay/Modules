<?php

if(!defined("PHORUM")) return;

function phorum_mod_bbcode_video_format($data)
{
    $PHORUM = $GLOBALS["PHORUM"];

    // These are used for building all the replacements that we need.
    $search  = array();
    $replace = array();

    // Handle YouTube [video] URLs.
    $search[] = "/\[video\]http:\/\/(\w+\.)?youtube.com\/watch\?v=([\w_-]+)[a-z0-9;\/\?:@=\&\$\-_\.\+!*'\(\),~%#]*\[\/video\]/is";
    $replace[] = "[video]youtube:$2[/video]";

    $search[] = "/\[video\]http:\/\/\w+\.?youtube.com\/v\/([\w_-]+)\[\/video\]/is";
    $replace[] = "[video]youtube:$1[/video]";

    // Handle Google [video] URLs.
    $search[] = "/\[video\]http:\/\/(video.google.[a-z]+)\/.*?docid=(-?\d+)[a-z0-9;\/\?:@=\&\$\-_\.\+!*'\(\),~%#]*\[\/video\]/is";
    $replace[] = "[video]google:$1:$2[/video]";

    // Handle Vimeo [video] URLs.
    $search[] = "/\[video\]http:\/\/(?:www.)?vimeo.com\/(\d+)\[\/video\]/";
    $replace[] = "[video]vimeo:$1[/video]";

    // Handle break.com URLs.
    $search[] = "/\[video\]http:\/\/embed.break.com\/(\w+)\[\/video\]/";
    $replace[] = "[video]break:$1[/video]";

    // Handle ebaumsworld.com URLs.
    $search[] = "/\[video\]http:\/\/(?:www\.)?ebaumsworld\.com\/(\d\d\d\d\/\d\d\/[\w\.-]+\.flv)\[\/video\]/";
    $replace[] = "[video]ebaumsworld:$1[/video]";

    // Handle plain URLs to videos.
    if (!empty($PHORUM["mod_bbcode_video"]["handle_plain_urls"]))
    {
        // YouTube URLs.
        $search[] = "/\[url\]http:\/\/(\w+\.)?youtube.com\/watch\?v=([\w_-]+)[a-z0-9;\/\?:@=\&\$\-_\.\+!*'\(\),~%#]*\[\/url\]/is";
        $replace[] = "[video]youtube:$2[/video]";

        $search[] = "/\[url=http:\/\/(\w+\.)?youtube.com\/watch\?v=([\w_-]+)[a-z0-9;\/\?:@=\&\$\-_\.\+!*'\(\),~%#]*\](.+?)\[\/url\]/is";
        $replace[] = "[video]youtube:$2[/video]<br/>$3";

        $search[] = "/http:\/\/(\w+\.)?youtube.com\/watch\?v=([\w_-]+)[a-z0-9;\/\?:@=\&\$\-_\.\+!*'\(\),~%#]*/is";
        $replace[] = "[video]youtube:$2[/video]";

        $search[] = "/http:\/\/\w+\.?youtube.com\/v\/([\w_-]+)/is";
        $replace[] = "[video]youtube:$1[/video]";

        // Google URLs.
        $search[] = "/\[url\]http:\/\/(video.google.[a-z]+)\/.*?docid=(-?\d+)[a-z0-9;\/\?:@=\&\$\-_\.\+!*'\(\),~%#]*\[\/url\]/is";
        $replace[] = "[video]google:$1:$2[/video]";
        $search[] = "/http:\/\/(video.google.[a-z]+)\/.*?docid=(-?\d+)[a-z0-9;\/\?:@=\&\$\-_\.\+!*'\(\),~%#]*/is";
        $replace[] = "[video]google:$1:$2[/video]";

        // Vimeo URLs.
        $search[] = "/\[url\]http:\/\/(?:www.)?vimeo.com\/(\d+)\[\/url\]/";
        $replace[] = "[video]vimeo:$1[/video]";
        $search[] = "/http:\/\/(?:www.)?vimeo.com\/(\d+)/";
        $replace[] = "[video]vimeo:$1[/video]";

        // break.com URLs.
        $search[] = "/\[url\]http:\/\/embed.break.com\/(\w+)\[\/url\]/";
        $replace[] = "[video]break:$1[/video]";
        $search[] = "/http:\/\/embed.break.com\/(\w+)\b/";
        $replace[] = "[video]break:$1[/video]";

        // break.com URLs.
        $search[] = "/\[url\]http:\/\/embed.break.com\/(\w+)\[\/url\]/";
        $replace[] = "[video]break:$1[/video]";
        $search[] = "/http:\/\/embed.break.com\/(\w+)\b/";
        $replace[] = "[video]break:$1[/video]";

        // ebaumsworld.com URLs.
        $search[] = "/\[url\]http:\/\/(?:www\.)?ebaumsworld\.com\/(\d\d\d\d\/\d\d\/[\w\.-]+\.flv)\[\/url\]/";
        $replace[] = "[video]ebaumsworld:$1[/video]";
        $search[] = "/http:\/\/(?:www\.)?ebaumsworld\.com\/(\d\d\d\d\/\d\d\/[\w\.-]+\.flv)\b/";
        $replace[] = "[video]ebaumsworld:$1[/video]";
    }

    // Final replacement for YouTube videos.
    $search[] = "/\[video\]youtube:([\w_-]+)\[\/video\]/is";
    $replace[] = "<br/><embed src=\"http://www.youtube.com/v/$1\" type=\"application/x-shockwave-flash\" width=\"425\" height=\"350\" wmode=\"transparent\"></embed><br/>";

    // Final replacement for Google videos.
    $search[] = "/\[video\]google:(video.google.[a-z]+):(-?\d+)\[\/video\]/is";
    $replace[] = "<br/><embed src=\"http://$1/googleplayer.swf?docId=$2\" type=\"application/x-shockwave-flash\" width=\"425\" height=\"350\" wmode=\"transparent\"></embed><br/>";

    // Final replacement for Vimeo videos.
    $search[] = "/\[video\]vimeo:(\d+)\[\/video\]/is";
    $replace[] = "<br/><embed src=\"http://www.vimeo.com/moogaloop.swf?clip_id=$1&amp;server=vimeo.com&amp;fullscreen=1\" type=\"application/x-shockwave-flash\" width=\"425\" height=\"350\" wmode=\"transparent\"></embed><br/>";

    // Final replacement for break.com videos.
    $search[] = "/\[video\]break:(\w+)\[\/video\]/is";
    $replace[] = "<br/><embed src=\"http://embed.break.com/$1\" type=\"application/x-shockwave-flash\" width=\"464\" height=\"392\" wmode=\"transparent\"></embed><br/>";

    // Final replacement for ebaumsworld.com videos.
    $search[] = "/\[video\]ebaumsworld:(\d\d\d\d\/\d\d\/[\w\.-]+)\.flv\[\/video\]/is";
    $replace[] = "<br/><embed src=\"http://www.ebaumsworld.com/mediaplayer.swf\" flashvars=\"file=/$1.flv&displayheight=321&image=/$1.jpg\" loop=\"false\" menu=\"false\" quality=\"high\" bgcolor\"#ffffff\" width=\"425\" height=\"345\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" wmode=\"transparent\"/></br>";

    // Handle unknown videos. Turn them into standard URLs.
    $search[]  = "/\[video\](.+)\[\/video\]/is";
    $replace[] = "video: [url]$1[/url]";

    // Run the replacements on the message bodies.
    foreach($data as $id => $message)
    {
      // Skip formatting if bbcode formatting was disabled for the post
      // (this is a feature of the BBcode module that we should honor).
      if (!empty($PHORUM["mod_bbcode"]["allow_disable_per_post"]) &&
        !empty($message['meta']['disable_bbcode'])) {
        continue;
      }

      if (isset($message["body"]) &&
          ($PHORUM["mod_bbcode_video"]["handle_plain_urls"] ||
           strstr($message['body'], "[video"))) {
        $data[$id]['body'] = preg_replace($search, $replace, $message['body']);
      }
    }

    return $data;
}

function phorum_mod_bbcode_video_javascript_register($data)
{
    if (empty($GLOBALS["PHORUM"]["mod_bbcode_video"]["enable_editor_tool"]))
        return $data;

    $data[] = array(
        "module" => "bbcode",
        "source" => "file(mods/bbcode_video/bbcode_video.js)"
    );

    return $data;
}

function phorum_mod_bbcode_video_editor_tool_plugin()
{
    global $PHORUM;

    if (empty($PHORUM["mod_bbcode_video"]["enable_editor_tool"]))
        return;

    editor_tools_register_tool(
        'bbcode_video',                 // Tool id
        'Video link',                   // Tool description
        './mods/bbcode_video/icon.gif', // Tool button icon
        'bbcode_video_editor_tool()'    // Javascript action on button click
    );

    editor_tools_register_translations(
        $PHORUM['DATA']['LANG']['mod_bbcode_video']
    );
}

?>
