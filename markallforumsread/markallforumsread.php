<?php
if(!defined("PHORUM")) return;

// Load default settings for the mod.
require_once("./mods/markallforumsread/defaults.php");

// Handle marking all forums read and set up template data for the index page.
// Note that this hook might not be called on the index page (in case there
// are no forums to display).
function phorum_mod_markallforumsread_index($forums)
{
    global $PHORUM;
    if (! $PHORUM["DATA"]["LOGGEDIN"]) return $forums;

    // Handle marking all forums read. We go through all the visible
    // forums and mark them read. After that, we redirect the user back
    // to the plain index URL, to prevent people from bookmarking the
    // URL including the mark all read argument in it.
    if (in_array("markallforumsread", $PHORUM["args"]))
    {
        // Retrieve a list of all forums in the current vroot.
        $allforums = phorum_db_get_forums(NULL, NULL, $PHORUM['vroot']);

        // Mark them all as read.
        foreach ($allforums as $id => $data)
        {
            if ($data["folder_flag"]) continue;

            // Mark forum read.
            phorum_db_newflag_allread($data["forum_id"]);

            // Clear newflags cache.
            if (isset($PHORUM["cache_newflags"]) && $PHORUM["cache_newflags"]){
                $cache_id = "{$data["forum_id"]}-{$PHORUM["user"]["user_id"]}";
                phorum_cache_remove("newflags", $cache_id);
                phorum_cache_remove("newflags_index", $cache_id);
            }
        }

        // Redirect back to the plain index URL.
        if (!empty($PHORUM["forum_id"]))
            $url = phorum_get_url(PHORUM_INDEX_URL, $PHORUM["forum_id"]);
        else
            $url = phorum_get_url(PHORUM_INDEX_URL);
        phorum_redirect_by_url($url);
        exit();
    }

    // Count the number of available new messages.
    $newmessage_count = 0;
    foreach ($forums as $id => $data) {
        if ($data["folder_flag"]) continue;
        if (! empty($data["new_messages"])) {
            $newmessage_count += $data["new_messages"];
        }
    }

    // Generate the URL for the mark read link.
    $PHORUM["DATA"]["MARKALLFORUMSREAD_URL"] =    // <-- deprecated
    $PHORUM["DATA"]["URL"]["MARKALLFORUMSREAD"] = // <-- 5.2 style URL var
        phorum_get_url(PHORUM_INDEX_URL, $PHORUM["forum_id"], "markallforumsread");

    // Format the default mark read link code.
    $PHORUM["DATA"]["MARKALLFORUMSREAD_LINK"] =
        '<div style="text-align:right; padding: 5px 0px">' .
        '<a href="' . $PHORUM["DATA"]["URL"]["MARKALLFORUMSREAD"] . '">' .
        $PHORUM["DATA"]["LANG"]["MarkAllForumsRead"] .
        '</a></div>';

    // Put the new message count in the template data.
    $PHORUM["DATA"]["MARKALLFORUMSREAD_NEWCOUNT"] = $newmessage_count;

    return $forums;
}

function phorum_mod_markallforumsread_after_header()
{
    $PHORUM = $GLOBALS["PHORUM"];

    if (phorum_page == 'index' && isset($PHORUM["DATA"]["MARKALLFORUMSREAD_LINK"])) {
        if (! $PHORUM["DATA"]["LOGGEDIN"]) return;
        if (! $PHORUM["mod_markallforumsread"]["show_after_header"]) return;
        if ($PHORUM["mod_markallforumsread"]["show_only_if_new"] &&
            $PHORUM["DATA"]["MARKALLFORUMSREAD_NEWCOUNT"] == 0) return;
        print $PHORUM["DATA"]["MARKALLFORUMSREAD_LINK"];
    }
}

function phorum_mod_markallforumsread_before_footer()
{
    $PHORUM = $GLOBALS["PHORUM"];

    if (phorum_page == 'index' && isset($PHORUM["DATA"]["MARKALLFORUMSREAD_LINK"])) {
        if (! $PHORUM["DATA"]["LOGGEDIN"]) return;
        if (! $PHORUM["mod_markallforumsread"]["show_before_footer"]) return;
        if ($PHORUM["mod_markallforumsread"]["show_only_if_new"] &&
            $PHORUM["DATA"]["MARKALLFORUMSREAD_NEWCOUNT"] == 0) return;
        print $PHORUM["DATA"]["MARKALLFORUMSREAD_LINK"];
    }
}

?>
