<?php
if (!defined('PHORUM')) return;

// Apply some default values to the settings.
if (!isset($PHORUM['mod_search_spelling']['language'])) {
    $PHORUM['mod_search_spelling']['language'] = '';
}
if (!isset($PHORUM['mod_search_spelling']['auto_display'])) {
    $PHORUM['mod_search_spelling']['auto_display'] = 1;
}

function phorum_mod_search_spelling_search_action($args)
{
    global $PHORUM;

    $query = urlencode($args['search']);
    $url = "http://www.google.com/search?q=$query";
    if (!empty($PHORUM['mod_search_spelling']['language'])) {
        $l = urlencode($PHORUM['mod_search_spelling']['language']);
        $url .= "&hl=$l&lr=lang_$l";
    }

    include "./include/api/http_get.php";
    $page = phorum_api_http_get($url);

    // Fill this explicitly to prevent undefed index warnings.
    $PHORUM['DATA']['SEARCH_SPELLING_LINK'] = '';

    if (preg_match('/<a href=[^>]+&amp;spell=1[^>]*>(.+?)<\/a>/i', $page, $m))
    {
        // Remove Google's markup
        $query = strip_tags($m[1]);

        // Restore special HTML characters.
        $query = trim(str_replace(
            array('&lt;', '&gt;', '&amp;', '&quot;'),
            array('<',    '>',    '&',     '"'),
            $query
        ));

        $url_parameters = array(
            PHORUM_SEARCH_URL,
            'search='.urlencode($query)
        );

        if (isset($PHORUM["hooks"]["search_redirect"])) {
            $url_parameters = phorum_hook("search_redirect", $url_parameters);
        }
        $search_url = call_user_func_array('phorum_get_url', $url_parameters);

        $PHORUM["DATA"]["SEARCH_SPELLING"] = array(
            "URL"   => $search_url,
            "QUERY" => htmlspecialchars($query)
        );

        if (empty($PHORUM['mod_search_spelling']['auto_display'])) {
            ob_start();
            include(phorum_get_template('search_spelling::did_you_mean'));
            $PHORUM['DATA']['SEARCH_SPELLING_LINK'] = ob_get_contents();
            ob_end_clean();
        }
    }

    return $args;
}

function phorum_mod_search_spelling_after_header()
{
    global $PHORUM;

    if (!empty($PHORUM['mod_search_spelling']['auto_display'])) {
        include(phorum_get_template('search_spelling::did_you_mean'));
    }
}

?>
