<?php

if(!defined("PHORUM")) return;

require_once("./mods/meta_description/defaults.php");

function set_meta_description($description)
{
    $stripped = preg_replace('!\s+!s', ' ', strip_tags(trim($description)));
    $GLOBALS["PHORUM"]["MOD_META_DESCRIPTION"] = htmlspecialchars(
        $stripped, ENT_COMPAT, $GLOBALS["PHORUM"]["DATA"]["HCHARSET"]
    );
}

function phorum_mod_meta_description_common()
{
    if (phorum_page == "list" || phorum_page == "read") { return; }

    // If a Phorum description was configured, we can use that one
    // to provide a meta description.
    $PHORUM = $GLOBALS["PHORUM"];
    if (isset($PHORUM["description"]) &&
        trim($PHORUM["description"]) != "") {
        set_meta_description($PHORUM["description"]);
    }
}

function phorum_mod_meta_description_list($threads)
{
    // If a forum description was configured, we can use that one
    // to provide a meta description.
    $PHORUM = $GLOBALS["PHORUM"];
    if (isset($PHORUM["DATA"]["DESCRIPTION"]) &&
        trim($PHORUM["DATA"]["DESCRIPTION"]) != "") {
        set_meta_description($PHORUM["DATA"]["DESCRIPTION"]);
    }

    return $threads;
}

function phorum_mod_meta_description_read($messages)
{
    if (phorum_page != 'read') return $messages;

    $PHORUM = $GLOBALS["PHORUM"];
    $settings = $PHORUM["mod_meta_description"];

    if ($PHORUM["threaded_read"]) {
        // Get the message that is currently open.
        $id = isset($PHORUM["args"][2])?$PHORUM["args"][2]:$PHORUM["args"][1];
        $body = $messages[$id]["body"];
    } else {
        // Get the first message from the message list.
        // That message is the message that started the thread.
        list ($id, $thread) = each($messages);
        $body = $thread["subject"] . ": " . $thread["body"];
    }

    // We need to format the body, to get rid of bbcode,
    // smileys, in body attachments, etc. We'll do that by
    // running strip_tags on the formatted data later on.
    $formatted = phorum_format_messages(array(1=>array("body"=>$body)));
    $body = $formatted[1]["body"];

    $trimmed = false;

    // Trim and remove newlines.
    $body = str_replace("\n", "", trim($body));

    // Collapse multiple spaces.
    $body = preg_replace("/\s+/", " ", $body);

    // Get the amount of requested paragraphs.
    $count = $settings["excerpt_paragraphs"];
    if ($count > 0) {
        $body = trim(strip_tags($formatted[1]["body"], "<br>"));
        $para = preg_split('!(<br\s*/?>\s*){2,}+!', $body, $count+1);
        if ($count < count($para)) {
            $body = implode(array_slice($para, 0, $count));
            $trimmed = true;
        }
    } else {
        $body = trim(strip_tags($formatted[1]["body"]));
    }

    // Trim the words down if needed.
    $count = $settings["excerpt_words"];
    if ($count > 0) {
        $words = explode(" ", $body, $count+1);
	if ($count < count($words)) {
            $body = implode(" ", array_slice($words, 0, $count));
            $trimmed = true;
	}
    }

    // Trim the characters down if needed.
    $count = $settings["excerpt_characters"];
    if ($count > 0 && strlen($body) > $count) {
        $body = substr($body, 0, $count);
        $trimmed = true;
    }
    if ($trimmed) {
        $body .= " ...";
    }

    set_meta_description($body);

    return $messages;
}

function phorum_mod_meta_description_start_output()
{
    global $PHORUM;

    if (isset($PHORUM["MOD_META_DESCRIPTION"])) {
      $PHORUM['DATA']['DESCRIPTION'] = $PHORUM["MOD_META_DESCRIPTION"];
    }
}

?>
