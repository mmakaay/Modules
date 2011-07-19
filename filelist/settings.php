<?php
    if (!defined("PHORUM_ADMIN")) return;

    print "<h1>File List Module Settings</h1>";

    if (count($_POST)) {
        $GLOBALS["PHORUM"]["mod_filelist"] = $_POST["filelist"];
        if(!phorum_db_update_settings(array("mod_filelist"=>$GLOBALS["PHORUM"]["mod_filelist"]))){
            phorum_admin_error("Database error while updating settings");
        } else {
            phorum_admin_okmsg("Settings successfully saved");
        }
    }

    include_once "./include/admin/PhorumInputForm.php";
    $frm =& new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "filelist");

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
        $frm->addrow($name, $frm->select_tag("filelist[{$node["forum_id"]}]", $choices, $filelist[$node["forum_id"]]));
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
