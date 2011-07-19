<?php

if (!defined("PHORUM")) return;

// Add the JavaScript code for this module to the pages.
function phorum_mod_embed_attachments_javascript_register($data)
{
    $data[] = array(
        "module" => "embed_attachments",
        "source" => "file(mods/embed_attachments/embed_attachments.js)"
    );
    return $data;
}

// Add a link to the body if auto add link is enabled.
function phorum_mod_embed_attachments_after_attach($args)
{
    $PHORUM = $GLOBALS['PHORUM'];

    if (empty($PHORUM["mod_embed_attachments"]["auto_add_link"])) {
        return $args;
    }

    list ($message, $attachment) = $args;

    // Create a safe to use version of the filename. This filename is
    // mainly used for clarity for the user in the attachment tag, so
    // he knows what attachment the link is pointing to. Therefore,
    // we can mangle the filename as much as we like.
    $esc = preg_replace("/[^\w_=+:\.-]/", "", $attachment["name"]);

    // Add the attachment link to the end of the body text.
    $message["body"] .= "\n[attachment " . $attachment["file_id"] . " $esc]";

    return array($message, $attachment);
}

// Remove [attachment ...] links for the detached attachment from the body.
function phorum_mod_embed_attachments_after_detach($args)
{
    $args[0]["body"] = preg_replace(
        '/\[attachment\s+'.$args[1]['file_id'].'\s+[^\]]+\]/',
        "", $args[0]["body"]
    );

    return $args;
}

// Add an extra button to the attachment button array for creating
// a link to an attachment in the message body.
function phorum_mod_embed_attachments_attachmentbuttons($attachment)
{
    // Setup template data.
    $PHORUM = $GLOBALS['PHORUM'];
    $PHORUM['DATA']['EMBED_ATTACHMENTS']['NAME'] =
        preg_replace("/[^\w_=+:\.-]/", "", $attachment["name"]);
    $PHORUM['DATA']['EMBED_ATTACHMENTS']['FILE_ID'] =
        $attachment['file_id'];

    // Display the attachment button template.
    include(phorum_get_template('embed_attachments::attachment_button'));

    return $attachment;
}

// Handle formatting of [attachment ...] links in the body.
function phorum_mod_embed_attachments_format($messages)
{
    global $PHORUM;

    foreach ($messages as $id => $message)
    {
        // Some quick skip options.
        if (!isset($message['body']) ||
            !strstr($message['body'], '[') ||
            empty($message['attachments'])) continue;

        // Find all embedded attachment links. If none are found, then
        // stop wasting time and go on to the next message.
        if (!preg_match_all("/\[attachment\s+(\d+)\s+([^\]]+)\]/",
                            $message['body'], $m)) continue;

        foreach ($m[0] as $match_id => $match)
        {
            $attachment_id = (int) $m[1][$match_id];

            // Lookup the attachment data.
            $attachment = NULL;
            $attachment_nr = NULL;
            foreach ($message['attachments'] as $aid => $adata) {
                if ($adata['file_id'] == $attachment_id) {
                   $attachment = $adata;
                   $attachment_nr = $aid;
                   break;
                }
            }

            // No attachment found in the message. Ignore and go on
            // to the next attachment link.
            if ($attachment === NULL) continue;

            // Currently, there is no central API for formatting messages,
            // causing format calls to not be complete at times. For the
            // time being, we catch format calls like these and generate
            // attachment URLs on the fly in case they are absent.
            if (empty($attachment['url'])) {
                $attachment["url"] = phorum_get_url(
                    PHORUM_FILE_URL,
                    'file='.$attachment["file_id"],
                    'filename='.urlencode($attachment["name"])
                );
            }
            if (empty($attachment['download_url'])) {
                $attachment["download_url"] = phorum_get_url(
                    PHORUM_FILE_URL,
                    'file='.$attachment["file_id"],
                    'filename='.urlencode($attachment["name"]),
                    'download=1'
                );
            }

            $description = htmlspecialchars($m[2][$match_id], ENT_COMPAT, $PHORUM["DATA"]["HCHARSET"]);
            $attachment['description'] = $description;

            // Give modules a chance to provide rendered content for
            // the attachment. This can be used by other modules to implement
            // embedded viewers or other link methods for the attachment
            // links in the body.

            // Because sometimes, the message_id does not match the
            // formatting call id.
            $message_copy = $message;
            if ($id != $message_copy['message_id']) {
                $message_copy['message_id'] = $id;
            }

            // Run the hook.
            $attachment['rendered'] = NULL;
            $attachment['rendered_raw'] = NULL;
            if (isset($PHORUM["hooks"]["render_embedded_attachment"])) {
                $attachment = phorum_hook(
                    'render_embedded_attachment',
                    $attachment,
                    $message_copy
                );
            }

            // If no module did render the embedded attachment, then use
            // our own rendering code. Simply turn the embedded attachment
            // into a link.
            if ($attachment['rendered'] === NULL &&
                $attachment['rendered_raw'] === NULL) {
                $url  = htmlspecialchars($attachment['url']);
                $attachment['rendered'] = '<a href="'.$url.'">'.$description.'</a>';
            }

            // Replace the embedded attachment. The final replacement
            // will be done in the format_fixup hook. If a hook set the
            // "rendered_raw" field, then that one will be used as the
            // replacement without changes. If "rendered" is used, then
            // a safe replacement tag will be generated by this module,
            // which will be replaced by the real content in the
            // format_fixup hook..
            if (isset($attachment['rendered_raw'])) {
                $tag = $attachment['rendered_raw'];
                $messages[$id]['body']
                    = str_replace($match, $tag, $messages[$id]['body']);
            } else {
                $tag = '[embed_attachments '.md5($attachment['rendered']).']';
                $PHORUM['mod_embed_attachments']['format_fixup'][$id][$tag]
                    = $attachment['rendered'];
                $messages[$id]['body']
                    = str_replace($match, $tag, $messages[$id]['body']);
            }

            // Pull the attachment from the attachment list, so it won't
            // be shown by the standard message rendering.
            unset($messages[$id]['attachments'][$attachment_nr]);
        }
    }

    return $messages;
}

function phorum_mod_embed_attachments_format_fixup($messages)
{
    $PHORUM = $GLOBALS['PHORUM'];

    if (!empty($PHORUM['mod_embed_attachments']['format_fixup'])) {
        $format_fixup = $PHORUM['mod_embed_attachments']['format_fixup'];
        foreach ($format_fixup as $id => $fixes) {
            foreach ($fixes as $tag => $content) {
                if (isset($messages[$id]['body'])) {
                    $messages[$id]['body']
                        = str_replace($tag, $content, $messages[$id]['body']);
                }
            }
        }
    }

    return $messages;
}

?>
