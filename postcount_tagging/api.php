<?php

function postcount_tagging_init()
{
    global $PHORUM;

    // Initialize settings array.
    if (empty($PHORUM['mod_postcount_tagging'])) {
        $PHORUM['mod_postcount_tagging'] = array();
    }
    if (empty($PHORUM['mod_postcount_tagging']['rules'])) {
        $PHORUM['mod_postcount_tagging']['rules'] = array();
    }
    if (empty($PHORUM['mod_postcount_tagging']['ignore'])) {
        $PHORUM['mod_postcount_tagging']['ignore'] = array();
    }
}

function postcount_tagging_rulesort($a, $b)
{
    $a1 = strtolower($a['name']);
    $b1 = strtolower($b['name']);
    if ($a1 == $b1) return 0;
    if ($a1 >  $b1) return +1;
    return -1;
}

function postcount_tagging_get_rules()
{
    global $PHORUM;
    if (empty($PHORUM['mod_postcount_tagging']['rules'])) {
        return array();
    }

    $rules = $PHORUM['mod_postcount_tagging']['rules'];
    uasort($rules, 'postcount_tagging_rulesort');

    return $rules;
}

function postcount_tagging_store_rule(&$rule)
{
    global $PHORUM;

    // Determine the rule id to use.
    if (empty($rule['id'])) {
        $store_id = 1;
        if (!empty($PHORUM['mod_postcount_tagging']['rules'])) {
            $keys = array_keys($PHORUM['mod_postcount_tagging']['rules']);
            rsort($keys, SORT_NUMERIC);
            $store_id = $keys[0] + 1;
        }
        $rule['id'] = $store_id;
    }

    $PHORUM['mod_postcount_tagging']['rules'][$rule['id']] = $rule;

    phorum_db_update_settings(array(
        'mod_postcount_tagging' => $PHORUM['mod_postcount_tagging']
    ));
}

function postcount_tagging_get_ignore_list()
{
    global $PHORUM;
    if (empty($PHORUM['mod_postcount_tagging']['ignore'])) {
        return array();
    }

    return $PHORUM['mod_postcount_tagging']['ignore'];
}

function postcount_tagging_store_ignore_list($ignore)
{
    global $PHORUM;

    $PHORUM['mod_postcount_tagging']['ignore'] = $ignore;

    phorum_db_update_settings(array(
        'mod_postcount_tagging' => $PHORUM['mod_postcount_tagging']
    ));
}


function postcount_tagging_delete_rule($rule)
{
    global $PHORUM;

    if (is_array($rule)) {
        if (empty($rule['id'])) return;
        $rule = $rule['id'];
    }
    if (empty($rule)) return;

    if (isset($PHORUM['mod_postcount_tagging']['rules'][$rule])) {
        unset($PHORUM['mod_postcount_tagging']['rules'][$rule]);
        phorum_db_update_settings(array(
            'mod_postcount_tagging' => $PHORUM['mod_postcount_tagging']
        ));
    }
}

// Update the counters for a post.
function postcount_tagging_modify($message, $delta)
{
    global $PHORUM;

    // Only for registered users.
    if (empty($message['user_id'])) return;
    $user = $PHORUM['user']['user_id'] == $message['user_id']
          ? $PHORUM['user']
          : phorum_api_user_get($message['user_id']);

    // Initialize counter structure.
    if (empty($user['mod_postcount_tagging'])) {
        $counts = array();
    } else {
        $counts = $user['mod_postcount_tagging'];
    }
    if (!isset($counts['forum']))  $counts['forum']  = array();
    if (!isset($counts['vroot']))  $counts['vroot']  = array();
    if (!isset($counts['global'])) $counts['global'] = 0;

    // Get the info for the forum.
    $forums = phorum_db_get_forums($message['forum_id']);
    $forum = $forums[$message['forum_id']];
    $vroot = $forum['vroot'];

    // Check for ignore rule.
    $ignores = $PHORUM['mod_postcount_tagging']['ignore'];
    if (isset($ignores[$message['forum_id']]) ||
        isset($ignores[$vroot])) return;

    // Update vroot counter.
    if(!empty($forums)) {
        $counts['vroot'][$vroot] += $delta;
        if ($counts['vroot'][$vroot] < 0) {
            $counts['vroot'][$vroot] = 0;
        }
    }

    // Update forum counter.
    $counts['forum'][$message['forum_id']] += $delta;
    if ($counts['forum'][$message['forum_id']] < 0) {
        $counts['forum'][$message['forum_id']] = 0;
    }

    // Update global counter.
    $counts['global'] += $delta;
    if ($counts['global'] < 0) {
        $counts['global']  = 0;
    }

    // Store the new counter info.
    phorum_api_user_save(array(
        'user_id' => $user['user_id'],
        'mod_postcount_tagging' => $counts
    ));

    // Make the info available to the template engine, if we're handling
    // the active user.
    if ($PHORUM['user']['user_id'] == $message['user_id']) {
        $PHORUM['user']['mod_postcount_tagging'] = $counts;
    }
}

// Check if a rule applies or not.
function postcount_tagging_process_rule($rule, $counters)
{
    global $PHORUM;

    // Check forum constraint.
    if ($rule['forum'] != -1) {
        if ($rule['forum'] != $PHORUM['forum_id']) return NULL;
    }
    // Check vroot constraint.
    elseif ($rule['vroot'] != -1) {
        if ($rule['vroot'] != $PHORUM['vroot']) return NULL;
    }

    // Determine the counter to look at.
    $count = NULL;
    switch ($rule['scope'])
    {
        case 'VROOT':
            $count = empty($counters['vroot'][$PHORUM['vroot']])
                   ? 0 : $counters['vroot'][$PHORUM['vroot']];
            break;

        case 'FORUM':
            $count = empty($counters['forum'][$PHORUM['forum_id']])
                   ? 0 : $counters['forum'][$PHORUM['forum_id']];
            break;

        case 'GLOBAL':
            $count = empty($counters['global'])
                   ? 0 : $counters['global'];
            break;
    }

    if ($count === NULL) return NULL; // should not happen

    // Check if any of the conditions match.
    $match = FALSE;
    if ( ($rule['>='] == '' || $count >= $rule['>=']) &&
         ($rule['>']  == '' || $count >  $rule['>'])  &&
         ($rule['<']  == '' || $count <  $rule['<'])  &&
         ($rule['<='] == '' || $count <= $rule['<=']) &&
         ($rule['=']  == '' || $count == $rule['=']) ) {
         $match = TRUE;
    }
    if (!$match) return NULL;

    return str_replace(array(
        '%count%',
        '%http_path%'
    ), array(
        $count,
        $PHORUM['http_path']
    ), $rule['tpl_html']);
}

?>
