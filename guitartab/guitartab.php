<?php

if (!defined("PHORUM")) return;

function phorum_mod_guitartab_posting_custom_action($post)
{
    // In case the [code] was incorrectly added to the message,
    // the user must have the chance to fix things by editing
    // the message.
    if (isset($post["message_id"]) && $post["message_id"]) return $post;

    $newbody = mod_guitartab_fixtabs($post["body"]);
    if ($newbody != NULL) $post["body"] = $newbody;
    return $post;
}

function mod_guitartab_fixtabs($body)
{
    // Add some extra data to the end of the message, to make parsing
    // end of message tokens easier.
    $body .= "\n<guitartab_stoptoken>";

    // Replace all newlines in the message with a custom break, which
    // can be used for easier tokenizing.
    $body = preg_replace("/\r?\n/", "<guitartab_break>", $body);

    // Tokenize the message.
    $tokens = array();
    while (preg_match('/^(.*?)(<guitartab_break>|\[\/?code\])(.*)$/', $body, $m)){
        $body = $m[3];
        if ($m[2] == '<guitartab_break>') {
            $tokens[] = $m[1] . '<guitartab_break>';
        } else {
            $tokens[] = $m[1];
            $tokens[] = $m[2];
        }
    }
    if ($body != '') {
        $tokens[] = $body;
    }

    // Process the tokens.
    $updated = false;
    $in_code = false;
    $tabline = 0;
    $chunk   = '';
    $body    = '';
    foreach ($tokens as $token)
    {
        // If we're not inside a [code] block ...
        if (! $in_code) {
            // ... and a [code] block is started ...
            if ($token == '[code]') {
                // ... then switch the code block status on.
                $in_code = true;
            // .. otherwise ...
            } else {
                // ... if the current token matches the TAB line format ...
                if (preg_match('/(-----|-\d{1,2}-|-\d{1,2}[a-z]\d{1,2}-)/', $token)) {
                    // ... and we weren't yet counting TAB lines, then this
                    // might be the first line of a TAB. Add the chunk we
                    // built up so far to the body, so from here on we will
                    // only have possible TAB lines in the chunk.
                    if (!$tabline) {
                        $body .= $chunk;
                        $chunk = '';
                    }
                    // Count those tab lines and build up our chunk.
                    $tabline ++;
                    $chunk .= $token;
                // ... If the token does not match a TAB line ...
                } else {
                    // ... then see if we're at the end of a TAB. A tab
                    // should contain 4 to 7 lines. Did we find exactly that
                    // many TAB lines? Then add a code block around them.
                    if ($tabline >= 4 && $tabline <= 7)
                    {
                        // If the current body ends in two newlines ...
                        if (preg_match('/^(.*<guitartab_break>)\s*<guitartab_break>$/',$body,$m)) {
                            // ... then we strip the last newline, so when
                            // adding the [code] block, it will start on the
                            // currently empty line.
                            $body = $m[1];
                        }

                        // Add a [code] block around the TAB.
                        $body .= '[code]<guitartab_break>'.
                                 $chunk .
                                 '[/code]';

                        // If there wasn't an empty line after the TAB,
                        // then introduce one here.
                        if (!preg_match('/^\s*<guitartab_break>$/', $token)) {
                            $body .= '<guitartab_break>';
                        }

                        $chunk = "";
                        $updated = true;
                    }

                    // Continue building our data chunk.
                    $chunk .= $token;

                    // Reset the TAB line counter, so the parsing mode
                    // will see the data gathered so far as a normal
                    // body chunk from here on.
                    $tabline = 0;
                }
            }
        }

        // If we're inside a [code] block ...
        if ($in_code) {
            // ... and if we encounter a [/code] ending ...
            if ($token == '[/code]') {
                // ... then add the data chunk we've built up to the
                // body and reset the in_code status flag.
                $chunk .= $token;
                $in_code = false;
                $body .= $chunk;
                $chunk = '';
            } else {
                // Otherwise, add the data to the data chunk.
                $chunk .= $token;
            }
        }
    }

    // Add any remaining chunk data to the body.
    if ($chunk != '') {
        $body .= $chunk;
    }

    // If the body was modified ...
    if ($updated)
    {
        // ... then restore the newlines and stop token ...
        $body = str_replace('<guitartab_break>', "\n", $body);
        $body = str_replace('<guitartab_stoptoken>', '', $body);

        // ... and return the new body.
        return $body;
    // Otherwise, return NULL to flag that nothing was changed.
    } else {
        return NULL;
    }
}

?>
