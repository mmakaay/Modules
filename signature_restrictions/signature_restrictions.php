<?php

function phorum_mod_signature_restrictions_cc_save_user($user)
{
    global $PHORUM;

    // We only need to handle checks if the signature is being saved.
    if (!isset($user['signature'])) return $user;

    // If another module returned an error already, then we won't run
    // our checks right now.
    if (isset($user["error"])) return $user;

    $settings  = $PHORUM['mod_signature_restrictions'];
    $lang      = $PHORUM['DATA']['LANG']['mod_signature_restrictions'];
    $signature = $user['signature'];

    // Trim the signature, while we are at it.
    $signature = trim($signature);

    // Put the trimmed info back in the data.
    $user["signature"] = $PHORUM['DATA']['PROFILE']['signature'] = $signature;

    // ----------------------------------------------------------------------
    // Check maximum signature length.
    // ----------------------------------------------------------------------

    if (!empty($settings['max_length'])) {
        if (strlen($signature) > $settings['max_length']) {
            $str = $lang['max_length'];
            $str = str_replace('%length%', $settings['max_length'], $str);
            $user['error'] = $str; 
        }
    }

    // ----------------------------------------------------------------------
    // Check maximum number of lines and maximum line length.
    // ----------------------------------------------------------------------

    // We only need to run this code if either max_lines or max_line_length
    // is in use.
    if (!isset($user['error']) && (
          !empty($settings['max_lines']) ||
          !empty($settings['max_line_length'])
        )) {

        // Split up the signature in separate lines.
        $lines = explode("\n", $signature); 

        // Check if too many lines were used.
        if (!empty($settings['max_lines']) &&
            count($lines) > $settings['max_lines']) {

            $str = $lang['max_lines'];
            $str = str_replace('%lines%', $settings['max_lines'], $str);
            $user['error'] = $str;
        }
        // Check if there are lines that are too long
        elseif (!empty($settings['max_line_length']))
        {
            $nr = 0;
            foreach ($lines as $line) {
               $nr++; 
               if (strlen($line) > $settings['max_line_length']) {
                    $str = $lang['max_line_length'];
                    $str = str_replace(
                      array('%line_length%', '%line_nr%'),
                      array($settings['max_line_length'], $nr),
                      $str
                    );
                    $user['error'] = $str;
                    break;
               }
            }
        }
    }

    // ----------------------------------------------------------------------
    // Check denying of images and/or markup code
    // ----------------------------------------------------------------------

    // We only need to run this code if either deny_images or deny_markup
    // is in use.
    if (!isset($user['error']) && (
          !empty($settings['deny_images']) ||
          !empty($settings['deny_markup'])
        )) {

        // Format the signature.
        include_once('./include/format_functions.php');
        $formatted = phorum_format_messages(array(0 => array(
            'author'  => '',
            'email'   => '',
            'subject' => '',
            'body'    => $user['signature']
        )));
        $signature = $formatted[0]['body'];

        // Remove newlines for better matching.
        $signature = str_replace("\n", "", $signature);

        // Check for images in the signature.
        if (!empty($settings['deny_images']) &&
            preg_match('/<\s*img\s/', $signature)) {
            $user['error'] = $lang['deny_images'];
        }

        // Check for markup code in the signature.
        if (!isset($user['error']) && !empty($settings['deny_markup']))
        {
            $stripped = strip_tags($signature, '<br>');
            if ($signature != $stripped)
            {
                // The user has used markup. Check if the users are allowed
                // to use markup after being signed up for a certain amount
                // of time.
                if (!empty($settings['markup_user_registered_days']))
                {
                    // Registration timestamp.
                    $tsregistered = $PHORUM["DATA"]["PROFILE"]["date_added"];

                    // Timestamp from which the user is allowed to use markup.
                    $tsvaliddate =
                      $tsregistered +
                      $settings['markup_user_registered_days'] * 60 * 60 * 24;

                    if (time() <= $tsvaliddate)
                    {
                        // Format the error to show user when they can start
                        // using markup.
                        $format = $PHORUM['short_date_time'];
                        $user['error'] = str_replace(
                            '%date%', phorum_date($format, $tsvaliddate),
                            $lang['markup_user_registered_days']
                        );
                    }
                }
                else
                {
                    $user['error'] = $lang['deny_markup'];
                }
            }
        }
    }

    return $user;
}

?>
