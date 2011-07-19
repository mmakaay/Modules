<?php

if(!defined("PHORUM")) return;

// Some static definitions for the module.
define("GOOGLE_URL", "http://www.google.com/search?q=");
define("GOOGLE_IMG", "./mods/bbcode_google/google.png");

// Add a stylesheet definition for the Google links.
function phorum_mod_bbcode_google_common()
{
    $PHORUM = $GLOBALS["PHORUM"];

    // Do not show the built-in style in case the built-in style is disabled.
    if (isset($PHORUM["mod_bbcode_google"]["builtin_style"]) &&
        ! $PHORUM["mod_bbcode_google"]["builtin_style"]) return;

    $padding_left = "5px";
    $background = "";

    // Add the Google logo if configured.
    if (! isset($PHORUM["mod_bbcode_google"]["show_logo"]) ||
        $PHORUM["mod_bbcode_google"]["show_logo"]) {
        $padding_left = "53px";
        $background =
            "background-image: url(" . GOOGLE_IMG . ");\n" .
            "background-repeat: no-repeat;\n" .
            "background-position: 3px 1px;\n";
    }

    ob_start(); ?>
    <style type="text/css">
    .phorum_mod_bbcode_google {
        padding: 0px 5px 1px 5px;
        padding-left: <?php print $padding_left ?>;
        background-color: white;
        border: 1px dotted #b0b0b0;
        <?php print $background ?>
    }
    </style> <?php
    $css = ob_get_contents();
    ob_end_clean();
    $GLOBALS["PHORUM"]["DATA"]["HEAD_TAGS"] .= $css;

}

// Format the Google links in the message body.
function phorum_mod_bbcode_google_format($data)
{
    $PHORUM = $GLOBALS["PHORUM"];

    foreach($data as $message_id => $message)
    {
        // Skip formatting if bbcode formatting was disabled for the post
        // (this is a feature of the BBcode module that we should honor).
        if (!empty($PHORUM["mod_bbcode"]["allow_disable_per_post"]) &&
            !empty($message['meta']['disable_bbcode'])) {
            continue;
        }

        if(isset($message["body"]))
        {
            $body = $message["body"];

            if (preg_match_all('/\[google\](.+?)\[\/google\]/', $body, $m))
            {
                for ($i= 0; isset($m[0][$i]); $i++)
                {
                    // Build the Google search URL.
                    $words = preg_split('/\s+/', $m[1][$i]);
                    foreach ($words as $id => $word) {
                        $words[$id] = urlencode($word);
                    }
                    $url = GOOGLE_URL . implode("+", $words);

                    // Determine the target for the link.
                    $target = "";
                    if (isset($PHORUM["mod_bbcode_google"]["links_in_new_window"]) && $PHORUM["mod_bbcode_google"]["links_in_new_window"]) {
                        $target = 'target="_blank"';
                    }

                    // Build the link.
                    $link = "<a rel=\"nofollow\" $target href=\"$url\">" .
                            $m[1][$i] .
                            "</a>";

                    // Add span around the link, so the link can be styled.
                    $link = "<span class=\"phorum_mod_bbcode_google\">$link</span>";

                    // Replace the link in the body.
                    $body = str_replace($m[0][$i], $link, $body);
                }
            }

            $data[$message_id]["body"] = $body;
        }
    }

    return $data;
}

// Editor tool implementation. The editor_tools module needs to
// be enabled for this to work.
function phorum_mod_bbcode_google_editor_tool_plugin()
{
    if (empty($GLOBALS["PHORUM"]["mod_bbcode_google"]["enable_editor_tool"]))
        return;

    editor_tools_register_jslib("./mods/bbcode_google/bbcode_google.js");

    editor_tools_register_tool(
        'google',                        // Tool id
        'Google link',                   // Tool description
        './mods/bbcode_google/icon.gif', // Tool button icon
        'bbcode_google_editor_tool()'    // Javascript action on button click
    );

    # This was a test to see if the Editor Tools help button could
    # be extended. A real help file would have to be written for this,
    # if neccessary. For now, I'll just disable this feature.
    #editor_tools_register_help(
    #    'How to supply google links',
    #    'http://www.google.com/'
    #);
}

?>
