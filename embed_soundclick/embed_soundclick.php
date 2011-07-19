<?php

if(!defined("PHORUM")) return;

function phorum_mod_embed_soundclick_css_register($data)
{
    // So we can use {URL->HTTP_PATH} in the templates.
    // This is a work around for Phorum 5.2.2-beta, which
    // missed the call to the URL building function.
    if (!isset($GLOBALS['PHORUM']['DATA']['URL']['HTTP_PATH'])) {
        phorum_build_common_urls();
    }

    $data['register'][] = array(
        'module' => 'embed_soundclick',
        'where'  => 'after',
        'source' => 'template(embed_soundclick::css)'
    );
    return $data;
}

// Format soundclick URLs in the message.
function phorum_mod_embed_soundclick_format($data)
{
    $PHORUM = $GLOBALS["PHORUM"];

    $GLOBALS['PHORUM']['mod_embed_soundclick']['format_fixup'] = array();

    // Run the replacements on the message bodies.
    foreach($data as $id => $message)
    {
        // Skip formatting if there is nothing to format.
        if (!isset($message['body']) ||
            !preg_match('/soundclick\.com/i',
                        $message['body'])) continue;

        // Preprocess soundclick URLs to a normalized format.
        // This normalized format will be replaced with an embedded
        // soundclick player in the format_fixup hook.

        // Audio URLs.
        $regexp = 'http://(?:www\.)?soundclick\.com/' .
                  '(?:share|bands/\w+\.cfm)\?' .
                  '(?:songid=|[\w=&;]+?songID=)(\d+)(?:[\w=&;])?';

        $data[$id]['body'] = preg_replace(
            '!\[url=('.$regexp.')\]([^\[\]]+)\[/url\]!ie',
            "'[soundclick_audio '.urlencode('\\1').' \\2 \\3]'",
            $data[$id]['body']
        );
        $data[$id]['body'] = preg_replace(
            '!\[url\]('.$regexp.')\[/url\]!ie',
            "'[soundclick_audio '.urlencode('\\1').' \\2 Soundclick MP3]'",
            $data[$id]['body']
        );
        $data[$id]['body'] = preg_replace(
            '!('.$regexp.')!ie',
            "'[soundclick_audio '.urlencode('\\1').' \\2 Soundclick MP3]'",
            $data[$id]['body']
        );

        // Video URLs.
        $regexp = 'http://(?:bands|www)\.soundclick\.com/(?:share/|bands/videos\.cfm\?[\w=&;]+?vidID=)(\d+)(?:[\w=&;]*)';
        $data[$id]['body'] = preg_replace(
            '!\[url=('.$regexp.')\]([^\[\]]+)\[/url\]!ie',
            "'[soundclick_video '.urlencode('\\1').' \\2 \\3]'",
            $data[$id]['body']
        );
        $data[$id]['body'] = preg_replace(
            '!\[url\]('.$regexp.')\[/url\]!ie',
            "'[soundclick_video '.urlencode('\\1').' \\2 Soundclick video]'",
            $data[$id]['body']
        );
        $data[$id]['body'] = preg_replace(
            "!($regexp)!ie",
            "'[soundclick_video '.urlencode('\\1').' \\2 Soundclick video]'",
            $data[$id]['body']
        );

        // Keep track of messages that possibly have to be updated in
        // the format_fixup hook. This way, we don't have to go over
        // all messages in that hook.
        $GLOBALS['PHORUM']['mod_embed_soundclick']['format_fixup'][] = $id;
    }

    return $data;
}

function phorum_mod_embed_soundclick_format_fixup($messages)
{
    $PHORUM = $GLOBALS['PHORUM'];

    foreach ($PHORUM['mod_embed_soundclick']['format_fixup'] as $id)
    {
        // Replace the normalized soundclick audio links.
        if (preg_match_all('/\[soundclick_audio (\S+) (\d+) ([^\]]+)\]/',
                           $messages[$id]['body'], $m))
        {
            foreach ($m[0] as $match_id => $match)
            {
                // Setup the template data.
                $PHORUM['DATA']['PLAYER'] = array(
                  'SONG_ID' => $m[2][$match_id],
                  'URL'     => urldecode($m[1][$match_id]),
                  'NAME'    => $m[3][$match_id]
                );

                // Load the template and catch the output.
                ob_start();
                include(phorum_get_template('embed_soundclick::audio_player'));
                $replace = ob_get_contents();
                ob_end_clean();

                $messages[$id]['body']
                    = str_replace($match, $replace, $messages[$id]['body']);
            }
        }

        // Replace the normalized soundclick video links.
        if (preg_match_all('/\[soundclick_video (\S+) (\d+) ([^\]]+)\]/',
                           $messages[$id]['body'], $m))
        {
            foreach ($m[0] as $match_id => $match)
            {
                // Setup the template data.
                $PHORUM['DATA']['PLAYER'] = array(
                  'VIDEO_ID' => $m[2][$match_id],
                  'URL'      => urldecode($m[1][$match_id]),
                  'NAME'     => $m[3][$match_id]
                );

                // Load the template and catch the output.
                ob_start();
                include(phorum_get_template('embed_soundclick::video_player'));
                $replace = ob_get_contents();
                ob_end_clean();

                $messages[$id]['body']
                    = str_replace($match, $replace, $messages[$id]['body']);
            }
        }
    }

    return $messages;
}

?>
