<?php

/**
 * Register the javascript code for this module.
 */
function phorum_mod_hide_reply_editor_javascript_register($data)
{
    $data[] = array(
        "module" => "hide_reply_editor",
        "source" => "template(hide_reply_editor::javascript)"
    );
    return $data;
}

/**
 * Forcibly enable the Phorum setting "reply on reply page".
 *
 * This module does not make sense if this option would be disabled.
 * It needs the editor to be on the read page to be useful.
 */
function phorum_mod_hide_reply_editor_page_read()
{
    global $PHORUM;
    $PHORUM['reply_on_read_page'] = TRUE;
}

/**
 * On a flat / hybrid view read page, wrap the posting editor in a div
 * of our own, that we can use to hide the editor from view.
 */
function phorum_mod_hide_reply_editor_get_template_file($data)
{
    global $PHORUM;
    $page = $data['page'];
    static $loaded = FALSE;

    if (phorum_page == 'read' && $page == 'posting')
    {
        if (!$loaded) {
            $data['page'] = 'hide_reply_editor::posting_wrapper';
            $loaded = TRUE;
        }
    }

    return $data;
}

?>
