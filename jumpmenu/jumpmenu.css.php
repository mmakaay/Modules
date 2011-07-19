div.mod_jumpmenu_menu
{
    text-align: left;
    position: absolute;
    padding: 2px;
    background-color:#eee;
    border:1px solid #777;
    font-family: arial, helvetica, sans-serif;
    display: none;
}

div.mod_jumpmenu_menu_content
{
}

a.mod_jumpmenu_menu_item
{
    font-size: 12px;
    float: none;
    display: block;
    padding: 3px 8px 3px 25px;
    text-decoration: none;
    white-space: nowrap;
}

a.mod_jumpmenu_menu_separator
{
    font-size: 12px;
    float: none;
    display: block;
    padding: 0 8px 0 25px;
    text-decoration: none;
    cursor: default;
    background-image: url(<?php print $PHORUM['http_path'] ?>/mods/jumpmenu/templates/separator.gif);
    background-position: center left;
    background-repeat: repeat-x;
}

a.mod_jumpmenu_menu_item:link,
a.mod_jumpmenu_menu_item:active,
a.mod_jumpmenu_menu_item:visited
{
    background-color: #f0f0f0;
    color: black;
}

a.mod_jumpmenu_menu_item.mod_jumpmenu_highlighted
{
    background-color: #666; 
    color: white;
    text-decoration: none;
}

a.mod_jumpmenu_folder
{
    background-image: url(<?php print $PHORUM['http_path'] ?>/mods/jumpmenu/templates/folder.gif);
    background-position: 5px 1px;
    background-repeat: no-repeat;
    cursor: default;
    color: black;
}

a.mod_jumpmenu_forum
{
    background-image: url(<?php print $PHORUM['http_path'] ?>/mods/jumpmenu/templates/forum.gif);
    background-position: 5px 1px;
    background-repeat: no-repeat;
    color: black;
}

