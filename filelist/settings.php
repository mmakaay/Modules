<?php
    if (!defined("PHORUM_ADMIN")) return;

    print "<h1>File List Module Settings</h1>";

    if (count($_POST))
    {
        $GLOBALS['PHORUM']['mod_filelist'] = $_POST['filelist'];
        $GLOBALS['PHORUM']['mod_filelist']['show_after_header'] =
            empty($_POST['show_after_header']) ? 0 : 1;
        $GLOBALS['PHORUM']['mod_filelist']['show_before_footer'] =
            empty($_POST['show_before_footer']) ? 0 : 1;

        if(!phorum_db_update_settings(array("mod_filelist"=>$GLOBALS["PHORUM"]["mod_filelist"]))){
            phorum_admin_error("Database error while updating settings");
        } else {
            phorum_admin_okmsg("Settings successfully saved");
        }
    }

    require_once('./include/admin/PhorumInputForm.php');
    $frm = new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "filelist");

    $frm->addbreak("Options for automatic displaying of the file list");

    $frm->addmessage("
        Using the options below, you can automatically display the file
        list in the page. If you want to manually position the file list
        in your pages, then you can do that by disabling these options and
        editing the templates. The code to put in the template is:
        <br/><br/>
        {HOOK \"tpl_filelist\" MOD_FILELIST LANG}<br/>
        </br>
        See the README that came with this module for more
        information on this.
    ");

    $frm->addrow("Show the file list automatically after the header", $frm->checkbox("show_after_header", "1", "", $PHORUM["mod_filelist"]["show_after_header"]));
    $frm->addrow("Show the file list automatically before the footer", $frm->checkbox("show_before_footer", "1", "", $PHORUM["mod_filelist"]["show_before_footer"]));

    $frm->addmessage("");

    $frm->addbreak("Configure where you want the file list to appear");

    // Build choices list for the page dropdown menu.
    $choices = array(
        ""      => "No file list",
        "FIRST" => "Only on the first page",
        "ALL"   => "On every page",
    );

    // Build the forum dynamically, based on the forum tree.
    $filelist = isset($GLOBALS["PHORUM"]["mod_filelist"])
              ? $GLOBALS["PHORUM"]["mod_filelist"]
              : array();
    $tree = phorum_mod_filelist_getforumtree();
    $forumlist = array();
    foreach ($tree as $data) {
        $level = $data[0];
        $node  = $data[1];
        $name = str_repeat("&nbsp;&nbsp;", $level);
        $name .= $node["folder_flag"] ? "Folder: " : "Forum: ";
        $name .= $node["name"];

        // No settings for folders. So display and continue.
        if ($node["folder_flag"]) {
            $frm->addrow($name);
            continue;
        }

        // Settings for forums.
        $value = empty($filelist[$node['forum_id']])
               ? "" : $filelist[$node['forum_id']];
        $frm->addrow($name, $frm->select_tag("filelist[{$node["forum_id"]}]",
        $choices, $value));
    }

    $frm->show();

    function phorum_mod_filelist_getforumtree()
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
        phorum_mod_filelist_mktree(0, $nodes, 0, $treelist);
        return $treelist;
    }

    // Recursive function for building the forum tree.
    function phorum_mod_filelist_mktree($level, $nodes, $node_id, &$treelist)
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
                phorum_mod_filelist_mktree($level, $nodes, $id, $treelist);
                $level --;
            }
        }
    }

?>
