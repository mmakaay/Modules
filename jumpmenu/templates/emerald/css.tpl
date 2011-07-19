{! this template file can be used to add override CSS code for the default }
{! CSS definition file mods/jumpmenu/templates/jumpmenu.css                }
{! Note: if you are using url(...) in the CSS code, then beware that this  }
{! CSS code runs relative to the Phorum root, and not to the jumpmenu's    }
{! template directory.                                                     }

a.mod_jumpmenu_root
{
    float: right;
    cursor: default;
}

div.mod_jumpmenu_menu
{
    border: 1px solid {border_color};
    {IF message_background_image}
      background-image: url('{message_background_image}');
    {/IF}
    background-repeat: repeat-x;
    background-color: {default_background_color};
    color: {default_font_color};
}

a.mod_jumpmenu_menu_item:link,
a.mod_jumpmenu_menu_item:active,
a.mod_jumpmenu_menu_item:visited {
    background-color: transparent;
}

a.mod_jumpmenu_menu_item.mod_jumpmenu_highlighted
{
    color: {border_font_color};
    background-color: {border_color};
}

a.mod_jumpmenu_folder
{
    background-image: url({URL->HTTP_PATH}/templates/emerald/images/folder.png);
    background-position: 5px 1px;
}

a.mod_jumpmenu_forum
{
    background-image: url({URL->HTTP_PATH}/templates/emerald/images/text_align_justify.png);
    background-position: 5px 1px;
}

