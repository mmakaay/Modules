<?php
    if (!defined("PHORUM_ADMIN")) return;
    ?>

    <h1>Topic Poll Module configuration</h1>

    Below, you see all the forums that are available. For each forum, you can configure
    if and how polls can be used by clicking the "Configure" button. If creating polls
    is enabled for a forum, then its name will be printed in bold.
    <br/><br/>

    <form action="<?php print htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST">
    <input type="hidden" name="phorum_admin_token"
           value="<?php print $PHORUM['admin_token'] ?>"/>
    <input type="hidden" name="module" value="modsettings"/>
    <input type="hidden" name="mod" value="topic_poll"/>

    <table border="0" cellspacing="2" cellpadding="2" class="input-form-table" width="100%">
    <tr class="input-form-tr">
      <td colspan="2" class="input-form-td-break">Select the forum you want to configure</td>
    </tr>
    <?php

    $tree = phorum_mod_topic_poll_getforumtree();
    foreach ($tree as $data)
    {
        list ($level, $node) = $data;

        if (! $node["folder_flag"]) {
            // Retrieve settings for the current forum and apply default settings.
            $settings = isset($PHORUM["mod_topic_poll"][$node["forum_id"]])
                      ? $PHORUM["mod_topic_poll"][$node["forum_id"]] : array();
            require_once('settings.default.php');
            foreach ($PHORUM["mod_topic_poll_defaults"] as $key => $val) {
                if (!isset($settings[$key])) $settings[$key] = $val;
            }
        }

        $name = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $level);
        $name .= '<img border="0" src="'.$PHORUM["http_path"].'/mods/topic_poll/images/' .
            ($node["folder_flag"] ? "folder.gif" : "forum.gif") . '"/> ' .
                 ((!$node["folder_flag"] && $settings["permission"] != 0) ? "<b>" : "") .
                 $node["name"] .
                 ((!$node["folder_flag"] && $settings["permission"] != 0) ? "</b>" : "");

        if ($node["folder_flag"]) { ?>
            <tr class="input-form-tr">
              <td valign="middle" align="left" class="input-form-td"
               nowrap="nowrap" colspan="2">
                <?php print $name ?>
              </td>
            </tr>
        <?php
        } else { ?>
            <tr class="input-form-tr">
              <td width="100%" valign="middle" align="left" class="input-form-td" nowrap="nowrap">
                <?php print $name ?>
              </td>
              <td valign="middle" alignt="right" class="input-form-td">
                <input type="submit" name="edit:<?php print $node["forum_id"] ?>"
                 value="Configure"/>
              </td>
            </tr>
        <?php
        }
    } ?>

    </table>
    </form>
    <?php

    // ----------------------------------------------------------------------
    // Functions for building a forum tree
    // ----------------------------------------------------------------------

    function phorum_mod_topic_poll_getforumtree()
    {
        // Retrieve all forums and create a list of all parents
        // with their child nodes.
        $forums = phorum_db_get_forums();
        $nodes = array();
        foreach ($forums as $id => $data) {
            $nodes[$data["parent_id"]][$id] = $data;
        }

        // Create the full tree of forums and folders.
        $treelist = array();
        phorum_mod_topic_poll_mktree(0, $nodes, 0, $treelist);
        return $treelist;
    }

    // Recursive function for building the forum tree.
    function phorum_mod_topic_poll_mktree($level, $nodes, $node_id, &$treelist)
    {
        // Should not happen but prevent warning messages, just in case...
        if (! isset($nodes[$node_id])) return;

        foreach ($nodes[$node_id] as $id => $node)
        {
            // Add the node to the treelist.
            $treelist[] = array($level, $node);

            // Recurse folders.
            if ($node["folder_flag"])
            {
                $level ++;
                phorum_mod_topic_poll_mktree($level, $nodes, $id, $treelist);
                $level --;
            }
        }
    }

?>
