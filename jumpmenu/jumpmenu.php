<?php

if (!defined("PHORUM")) return;

// Register the additional CSS code for this module.
function phorum_mod_jumpmenu_css_register($data)
{
    if ($data['css'] != 'css') return $data;

    $data['register'][] = array(
        "module" => "jumpmenu",
        "where"  => "after",
        "source" => "file(mods/jumpmenu/jumpmenu.css.php)"
    );

    $data['register'][] = array(
        "module" => "jumpmenu",
        "where"  => "after",
        "source" => "template(jumpmenu::css)"
    );

    return $data;
}

// Register the additional JavaScript code for this module.
function phorum_mod_jumpmenu_javascript_register($data)
{
    $data[] = array(
        "module" => "jumpmenu",
        "source" => "file(mods/jumpmenu/jumpmenu.js)"
    );
    return $data;
}

// Construct the forum menu drop down box.
function phorum_mod_jumpmenu_common()
{
    global $PHORUM;

    // ----------------------------------------------------------------------
    // Build HTML code for the menu.
    // ----------------------------------------------------------------------

    $forum_info = array();
    $menus = array();

    // Load the forum list.
    // The two different constructions are used for making this module
    // compatible with both Phorum 5.2 and 5.3.
    if (file_exists('./include/forum_functions.php')) {
        include "./include/forum_functions.php";
        $forums = phorum_build_forum_list();
    } else {
        require_once PHORUM_PATH.'/include/api/forums.php';
        $forums = phorum_api_forums_tree();
    }

    if (count($forums) == 0) return;

    $folders = array();
    foreach ($forums as $id => $forum)
    {
        $parent = $forum['parent_id'];
        if (!isset($menus[$parent])) {
            $menus[$parent] = array();
        }

        if ($forum['folder_flag']) {
            $class = 'mod_jumpmenu_folder';
            $href  = '';
            $rel   = 'mod_jumpmenu_folder[' .
                       $forum['forum_id']. ',' .
                       $forum['parent_id'].
                     ']';

            // Remember the folders the we see.
            if (isset($folders[$parent])) {
                $folders[$parent][] = $forum['forum_id'];
            } else {
                $folders[$parent] = array($forum['forum_id']);
            }
        } else {
            $rel   = 'mod_jumpmenu_forum';
            $class = 'mod_jumpmenu_forum';
            $href  = 'href="'.phorum_get_url(PHORUM_LIST_URL, $forum['forum_id']).'"';
            $forum_info[] = $forum['forum_id'] . ":" . $forum['parent_id'];
        }

        $menus[$parent][$forum['forum_id']] =
            "<a $href rel=\"$rel\" " .
            "id=\"mod_jumpmenu_item_{$forum['forum_id']}\" " .
            "class=\"mod_jumpmenu_menu_item $class\">" .
            $forum['name'] .
            "</a>";
    }

    // Allow other modules to add menu items to the root menu.
    // They can fill the array with extra items. Each item should be an
    // array, containing the fields:
    //
    // - name: the name of the menu item
    // - url: the URL to jump to when the link is clicked
    // - class (optional): CSS class to add to the default menu item classlist
    //
    if (isset($PHORUM['hooks']['jumpmenu_add'])) {
        $extra_items = phorum_hook('jumpmenu_add', array());
    }
    if (!empty($extra_items))
    {
        $menus[$PHORUM['vroot']]["separator"] =
            '<a href="#" rel="mod_jumpmenu_forum" ' .
            'class="mod_jumpmenu_menu_separator">&nbsp;</a>';
        $id = 0;
        foreach ($extra_items as $item)
        {
            if (!isset($item['url']))   $item['url']   = '#';
            if (!isset($item['name']))  $item['name']  = '[no name set for item!]';
            if (!isset($item['class'])) $item['class'] = '';

            $class = "mod_jumpmenu_menu_item mod_jumpmenu_forum {$item['class']}";

            $id++;
            $menus[$PHORUM['vroot']]["extra$id"] =
            "<a href=\"{$item['url']}\" rel=\"mod_jumpmenu_forum\" " .
            "id=\"mod_jumpmenu_item_extra$id\" " .
            "class=\"$class\">{$item['name']}</a>";
        }
    }

    foreach ($menus as $id => $menu)
    {
        // Drop empty folders.
        if (isset($folders[$id])) {
            foreach ($folders[$id] as $relid) {
                if (empty($menus[$relid])) unset($menus[$id][$relid]);
            }
        }

        if (!empty($menus[$id])) {
            $menus[$id] =
                "<div rel=\"mod_jumpmenu_menu\" class=\"mod_jumpmenu_menu\" " .
                "id=\"mod_jumpmenu_menu_$id\">\n" .
                " <div class=\"mod_jumpmenu_menu_content\" " .
                "id=\"mod_jumpmenu_menu_content_$id\">" .
                implode('', $menus[$id]) .
                '</div></div>';
        } else {
            unset($menus[$id]);
        }
    }

    $setvroot = '<script type="text/javascript">' .
                'PhorumJumpMenu.vroot = ' . $PHORUM['vroot'] .
                '</script>';

    $PHORUM['DATA']['JUMPMENU'] = $setvroot . implode('', $menus);
}

// Load the forum jump menu code before showing the footer.
function phorum_mod_jumpmenu_before_footer()
{
    global $PHORUM;

    if (empty($PHORUM['DATA']['JUMPMENU'])) return;

    print $PHORUM['DATA']['JUMPMENU'];

    include phorum_get_template("jumpmenu::before_footer");

    print "<script type=\"text/javascript\">\n" .
          "\$PJ(document).ready(function(){\n" .
          "    // Make sure that the menus are in the top level #phorum elt.\n" .
          "    // This is needed to make positioning of the menus robust.\n" .
          "    \$PJ('div.mod_jumpmenu_menu').each(function () {\n" .
          "        \$PJ('body').append(this);\n" .
          "    });\n" .
          "    // Initialize the Phorum jumpmenu.\n" .
          "    PhorumJumpMenu.init();\n" .
          "});\n" .
          "</script>\n";
}
?>
