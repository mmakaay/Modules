<?php
    include_once "./include/admin/PhorumInputForm.php";
    $frm = new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "banner_manager");
    $frm->hidden("panel", "pages");
?>
    On this page, you can link the banner blocks that you
    defined to Phorum pages, so they will be displayed
    on those pages.
    <br/><br/>

<?php
    if (count($_POST)) {
        $settings =& $GLOBALS["PHORUM"]["mod_banner_manager"];
        $settings["links"] = $_POST["page"];
        banner_manager_save_settings();
        print "<div class=\"PhorumAdminOkMsg\">Settings successfully saved</div>\n";
    }

    // Build the dynamic part of the page list.
    $tree = phorum_mod_banner_manager_getforumtree();
    $dynamic_pages = array();
    foreach ($tree as $data) {
        $level = $data[0];
        $node  = $data[1];
        $name = str_repeat("&nbsp;&nbsp;", $level);
        $name .= $node["folder_flag"] ? "Folder: " : "Forum: ";
        $name .= $node["name"];
        $dynamic_pages[$node["forum_id"]] = $name;
    }

    // Build the list of pages for which we can show a banner block.
    $pagelist = array(
      "General pages" => array(
        "0"              => "Forum startpage",  // "forum id" 0
        "login"          => "Login page",
        "register"       => "New account registration",
        "pm"             => "Private messages",
        "profile"        => "Profile view page",
        "control"        => "User control center",
      ),
      "Forum and Folder specific pages" => $dynamic_pages
    );

    // Build the list of choices for the page dropdown menu.
    $choices = array(
        "NULL" => "No banner",
        "RANDOM" => "Random banner, not including date specific banners",
        "RANDOM_INC_DATES" => "Random banner, including date specific banners",
        "DATES" => "Date specific banners only",
        " " => "" // space, to prevent selection if the var is empty
    );

    foreach ($GLOBALS["PHORUM"]["mod_banner_manager"]["banners"] as $data) {
        $choices["BANNER:{$data["id"]}"] = htmlspecialchars($data["name"]);
    }

    $links = $GLOBALS["PHORUM"]["mod_banner_manager"]["links"];
    foreach ($pagelist as $section => $pages) {
        $frm->addbreak($section);
        foreach ($pages as $id => $desc) {
            $frm->addrow($desc, $frm->select_tag("page[$id]", $choices, $links[$id]));
        }
    }

    $frm->show();

    function phorum_mod_banner_manager_getforumtree()
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
        phorum_mod_banner_manager_mktree(0, $nodes, 0, $treelist);
        return $treelist;
    }

    // Recursive function for building the forum tree.
    function phorum_mod_banner_manager_mktree($level, $nodes, $node_id, &$treelist)
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
                phorum_mod_banner_manager_mktree($level, $nodes, $id, $treelist);
                $level --;
            }
        }
    }
?>
