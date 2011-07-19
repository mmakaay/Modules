<?php

/**
 * Register the javascript code for this module.
 */
function phorum_mod_quick_reply_javascript_register($data)
{
    // The main JavaScript code for this module, which implements the
    // Quick Reply editor for the Emerald template.
    $data[] = array(
        "module" => "quick_reply",
        "source" => "file(mods/quick_reply/quick_reply.js.php)"
    );

    // Allow the module template to override functionality from the
    // main JavaScript code.
    $data[] = array(
        "module" => "quick_reply",
        "source" => "template(quick_reply::javascript)"
    );

    return $data;
}

/**
 * Register the CSS code for this module.
 */
function phorum_mod_quick_reply_css_register($data)
{
    if ($data['css'] != 'css') return $data;

    $data['register'][] = array(
        "module" => "quick_reply",
        "where"  => "after",
        "source" => "template(quick_reply::css)"
    );

    return $data;
}

/**
 * Tweak Spam Hurdles settings to make the quick reply editor work.
 */
function phorum_mod_quick_reply_common()
{
    global $PHORUM;
    if (empty($PHORUM['user']['user_id'])) return;
}

/**
 * On the read page, add a bit of code to the message bodies, which can
 * be used by the JavaScript for this module to identify the message
 * for which a "reply" or "quote" link is clicked and to find the outer
 * message container, in which the editor will be put after clicking
 * such link.
 */
function phorum_mod_quick_reply_format_fixup($messages)
{
    global $PHORUM;
    if (empty($PHORUM['user']['user_id'])) return $messages;

    if (phorum_page != 'read') return $messages;

    foreach ($messages as $id => $message)
    {
        if (!isset($message['message_id']) || !isset($message['body'])) {
            continue;
        }

        $messages[$id]['body'] .=
            '<a rel="' .
            $message['forum_id'] . ' ' .
            $message['thread'] . ' ' .
            $message['message_id'] .
            '" class="quick_reply_info"></a>';
    }

    return $messages;
}

/**
 * Add our editor to the read page.
 */
function phorum_mod_quick_reply_before_footer()
{
    global $PHORUM;
    if (empty($PHORUM['user']['user_id'])) return;

    if (phorum_page == 'read') {
        include phorum_get_template('quick_reply::posting');
    }
}

/**
 * After posting with the quick reply editor, we redirect the user to
 * his new message. Jumping back to the forum index would not really
 * match the functionality.
 */
function phorum_mod_quick_reply_posting_init()
{
    global $PHORUM;
    if (empty($PHORUM['user']['user_id'])) return;

    if (!empty($_POST['is_quick_reply']) && empty($_POST['preview']))
    {
        // Convert the "quick_posting" spam hurdle data to
        // "posting" spam hurdle data (for Spam Hurdles 2.0.0 and up).
        if (file_exists('./mods/spamhurdles/api.php')) {
            require_once './mods/spamhurdles/api.php';
            $data = spamhurdles_api_get_formdata('quick_posting');
            $data['id'] = 'posting';
            $_POST['spamhurdles_posting'] = spamhurdles_encrypt($data);
        }

        mod_quick_reply_tweak_spamhurdles();
        $PHORUM['redirect_after_post'] = 'read';
    }
}

/**
 * A Phorum Ajax call that allows us to retrieve the form field values
 * that we have to use to reply to a given message.
 */
function phorum_mod_quick_reply_ajax_init_editor($args)
{
    global $PHORUM;
    $message_id = phorum_ajax_getarg('message_id', 'int>0');
    $forum_id   = phorum_ajax_getarg('forum_id',   'int>0');

    mod_quick_reply_tweak_spamhurdles();

    if ($PHORUM['forum_id'] != $forum_id)
    {
        $PHORUM['forum_id'] = $forum_id;
        $forums = phorum_db_get_forums($forum_id);
        $PHORUM = array_merge($PHORUM, $forums[$forum_id]);
    }

    // Setup the posting variables as required for the message_id.
    $PHORUM["postingargs"] = array(
        0 => $forum_id,
        1 => 'reply',
        2 => $message_id,
        'as_include' => TRUE
    );

    ob_start();
    include './posting.php';
    ob_end_clean();

    if ($PHORUM['posting_template'] == 'message')
    {
        $error = 'Unknown error in phorum_mod_quick_reply_ajax_init_editor()';
        if (isset($PHORUM['DATA']['ERROR'])) {
            $error = $PHORUM['DATA']['ERROR'];
        } elseif (isset($PHORUM['DATA']['OKMSG'])) {
            $error = $PHORUM['DATA']['OKMSG'];
        }
        return phorum_ajax_error($error);
    }
    else
    {
        $posting = $PHORUM['DATA']['POSTING'];
        $post_vars = $PHORUM['DATA']['POST_VARS'];

        // We add the user defaults for following topics as hidden fields.
        // These are checkboxes in the original editor form, but we do not
        // show them in the quick editor to prevent the interface from
        // getting cluttered.
        if (!empty($posting['subscription']))
        {
            $post_vars .=
                '<input type="hidden" name="subscription_follow" value="1"/>';

            if (!empty($PHORUM['DATA']['OPTION_ALLOWED']['subscribe_mail']) &&
                $posting['subscription'] == 'message') {
                $post_vars .= '<input type="hidden" ' .
                              'name="subscription_mail" value="1"/>';
            }
        }

        // Also add the user default for adding the signature to the message.
        if (!empty($posting['show_signature'])) {
            $post_vars .=
                '<input type="hidden" name="show_signature" value="1"/>';
        }

        // Retrieve Spam Hurdles information if needed.
        $spam_hurdles = '';
        if (!empty($PHORUM['mods']['spamhurdles']))
        {
            require_once('./mods/spamhurdles/spamhurdles.php');

            mod_quick_reply_tweak_spamhurdles();

            ob_start();
            // Spam Hurdles version 1.1.6 and before.
            if (function_exists('phorum_mod_spamhurdles_init')) {
                phorum_mod_spamhurdles_init("posting");
                phorum_mod_spamhurdles_build_form("posting");
                phorum_mod_spamhurdles_before_footer();
            }
            // Spam Hurdles version 1.1.7 and above.
            elseif (function_exists('spamhurdles_init')) {
                spamhurdles_init("posting");
                spamhurdles_build_form("posting");
                phorum_mod_spamhurdles_before_footer();
            }
            // Spam Hurdles version 2.0.0 and above.
            elseif (function_exists('spamhurdles_api_init')) {
                $data = spamhurdles_api_init(
                    'quick_posting',
                    spamhurdles_get_hurdles_for_form('posting')
                );

                // Output the required form data.
                print spamhurdles_api_build_form($data);
                print spamhurdles_api_build_after_form($data);
            }

            $spam_hurdles = ob_get_contents();
            ob_end_clean();
        }

        // For javascript, the subject does not need to be HTML encoded.
        $subject = htmlspecialchars_decode($posting['subject'], ENT_COMPAT);

        phorum_ajax_return(array(
            'post_vars'    => $post_vars,
            'subject'      => $subject,
            'url'          => phorum_get_url(PHORUM_POSTING_ACTION_URL),
            'spam_hurdles' => $spam_hurdles
        ));
    }
}

/**
 * Tweak the settings for the Spam Hurdles module to make it work
 * for the Quick Reply editor.
 */ 
function mod_quick_reply_tweak_spamhurdles()
{
    global $PHORUM;
    if (empty($PHORUM['user']['user_id'])) return;

    // Spam Hurdles before version 2.0.0.
    if (function_exists('spamhurdles_api_init'))
    {
        // We do not want to add a captcha for registered users to
        // the Quick Reply editor. Therefore, we disable it forcibly.
        $PHORUM['mod_spamhurdles']['posting_captcha'] = 'none';

        // Well, the name says it all. We can implement this hurdle,
        // but then we need to modify the quick editor to have a disabled
        // button during the time that spam hurdles would block the message
        // as being posted too quickly. For now, we accept this as a
        // solution to prevent spam blocks. 
        $PHORUM['mod_spamhurdles']['blockquickpost'] = 'none';

        // The Spam Hurdles module needs some changes to make it
        // possible to use multiple forms on a single page.
        // Because of this, we now have to disable the MD5 signing check.
        $PHORUM['mod_spamhurdles']['jsmd5check'] = 'none';
    }

    // In Spam Hurdles 2.0.0 and beyond, CAPTCHA's can be used in
    // the quick reply. To prevent the reply box from growing too large,
    // we empty the CAPTCHA explanation text here.
    $PHORUM["DATA"]["LANG"]["mod_spamhurdles"]["CaptchaExplain"] = '';
    $PHORUM["DATA"]["LANG"]["mod_spamhurdles"]["CaptchaUnclearExplain"] = '';
}

?>
