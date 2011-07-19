<?php

if(!defined("PHORUM")) return;

// Set empty body to "n/t" if empty bodies are allowed, so the body
// checks won't complain about an empty body later on.
// Remember the empty body in $_POST["empty_body"] for the
// phorum_mod_empty_bodies_post() function.
function phorum_mod_empty_bodies_common ()
{
    if (count($_POST) == 0 || ! isset($_POST["body"]) ||
        (phorum_page != "post"  && phorum_page != "edit" &&
         phorum_page != "reply" && phorum_page != "moderation"))
        return;

    if (trim($_POST["body"]) == "" ||
        (isset($_POST["empty_body"]) && $_POST["empty_body"] == 1 && trim($_POST["body"]) == "n/t")) {
        $_POST["body"] = "n/t";
        $_POST["empty_body"] = 1;
    } else {
        $_POST["empty_body"] = 0;
    }
}

// Strip "n/t" from bodies if it was put in there by this module,
// before showing the editor. This way, the body will really be
// empty when editing empty messages.
function phorum_mod_empty_bodies_before_editor($data)
{
    // Make sure that when we are in the process of editing a message,
    // the meta variable is updated to the editor state.
    if (isset($_POST["empty_body"])) {
        $value = $_POST["empty_body"];
        $data["meta"]["mod_empty_bodies"]["body_empty"] = $value;
    }

    if ((isset($_POST["empty_body"]) && $_POST["empty_body"]) ||
        (isset($data["meta"]["mod_empty_bodies"]["body_empty"]) &&
         $data["meta"]["mod_empty_bodies"]["body_empty"])) {
        // Other mods might have added extra data to the message,
        // so we look at the string the body starts with and not
        // only compare the body to "n/t".
        if (substr($data["body"],0,3) == "n/t") {
            $data["body"] = substr($data["body"], 3);
        }
    }

    return $data;
}

// Set meta information in case we're handling an empty body, so
// template builders can act on empty bodies in their templates.
function phorum_mod_empty_bodies_post($data)
{
    if ($data["body"] == "n/t" &&
        isset($_POST["empty_body"]) && $_POST["empty_body"]) {
        $data["meta"]["mod_empty_bodies"]["body_empty"] = 1;
    } else {
        unset($data["meta"]["mod_empty_bodies"]);
    }
    return $data;
}

// Format the messages, based on the meta data.
function phorum_mod_empty_bodies_format($args)
{
    $PHORUM = $GLOBALS["PHORUM"];

    foreach ($args as $id => $data)
    {
        // Make sure that when we are in the process of editing a message,
        // the meta variable is updated to the editor state.
        if (isset($_POST["empty_body"])) {
            $value = $_POST["empty_body"];
            $data["meta"]["mod_empty_bodies"]["body_empty"] = $value;
        }

        if (isset($data["meta"]["mod_empty_bodies"]["body_empty"]) &&
            $data["meta"]["mod_empty_bodies"]["body_empty"]) {

            $args[$id]["subject"] .= $PHORUM["DATA"]["LANG"]["mod_empty_bodies"]["subject_marker"];

            // We're done if we have no body in our data.
            if (! isset($data["body"])) continue;

            // We trim, because the bbcode mod introduces a space at
            // the start of the body.
            $body = trim($data["body"]);

            // Strip the n/t from the body.
            if (substr($body,0,3) == "n/t") $body = substr($body, 3);

            // Now we remove phorum's breaks. These might be here in case
            // the message was edited, to separate the edit message from
            // the body. In this case, we want the body as small as possible,
            // so we remove the additional breaks.
            $body = preg_replace('/^<phorum break>\n/m', '', $body);

            // If the resulting body is not empty (might be because of an
            // editmessage or another module adding data), then add a break
            // to the start of it, to separate it from the body_marker.
            $body = trim($body);
            if ($body != "") $body = "<phorum break>$body";

            // Store the formatted body.
            $args[$id]["body"] = $PHORUM["DATA"]["LANG"]["mod_empty_bodies"]["body_marker"] . $body;
        }
    }

    return $args;
}

?>
