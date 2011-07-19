<?php

if (!defined('PHORUM')) return;

require_once("./mods/postcount_tagging/api.php");

// Handle module installation:
// Load the module installation code if this was not yet done.
// The installation code will take care of automatically adding
// the custom profile field that is needed for this module.
if (! isset($PHORUM["mod_postcount_tagging_installed"]) ||
    ! $PHORUM["mod_postcount_tagging_installed"]) {
    include("./mods/postcount_tagging/install.php");
}

// Update the post counters for the user.
function phorum_mod_postcount_tagging_after_post($message)
{
    // Do not count the post when it is not yet approved.
    if ($message['status'] != PHORUM_STATUS_APPROVED) {
        return $message;
    }

   postcount_tagging_modify($message, +1);
   return $message;
}

function phorum_mod_postcount_tagging_after_approve($data)
{
    list ($message, $approve_type) = $data;

    // Only count the post if it was on hold
    // (fresh post, not yet approved).
    if ($message['status'] == PHORUM_STATUS_HOLD) {
        postcount_tagging_modify($message, +1);
    }

   return $data;
}

function phorum_mod_postcount_tagging_profile($profile)
{
    global $PHORUM;

    if (empty($PHORUM['mod_postcount_tagging']['rules']) ||
        empty($profile['mod_postcount_tagging'])) return $profile;

    $counters = $profile['mod_postcount_tagging'];

    foreach ($PHORUM['mod_postcount_tagging']['rules'] as $rule)
    {
        if (empty($rule['enable_profile'])) continue;

        $value = postcount_tagging_process_rule($rule, $counters);

        if ($value !== NULL) {
            $profile[$rule['tpl_var']] = $value;
        }
    }

    return $profile;
}

function phorum_mod_postcount_tagging_read($messages)
{
    global $PHORUM;

    if (empty($PHORUM['mod_postcount_tagging']['rules'])) return $messages;

    foreach ($PHORUM['mod_postcount_tagging']['rules'] as $rule)
    {
        if (empty($rule['enable_read'])) continue;

        foreach ($messages as $id => $message)
        {
            if (empty($message['user_id']) ||
                empty($message['user']['mod_postcount_tagging'])) continue;

            $counters = $message['user']['mod_postcount_tagging'];
            $value = postcount_tagging_process_rule($rule, $counters);

            if ($value !== NULL) {
                $messages[$id]['user'][$rule['tpl_var']] = $value;
            }
        }
    }

    return $messages;
}

function phorum_mod_postcount_tagging_start_output()
{
    global $PHORUM;

    if (empty($PHORUM['mod_postcount_tagging']['rules']) ||
        empty($PHORUM['user']['user_id']) ||
        empty($PHORUM['user']['mod_postcount_tagging'])) return;

    $counters = $PHORUM['user']['mod_postcount_tagging'];

    foreach ($PHORUM['mod_postcount_tagging']['rules'] as $rule)
    {
        if (empty($rule['enable_user'])) continue;

        $value = postcount_tagging_process_rule($rule, $counters);

        if ($value !== NULL) {
            $PHORUM['user'][$rule['tpl_var']] = $value;
            $PHORUM['DATA']['USER'][$rule['tpl_var']] = $value;
        }
    }
}

?>
