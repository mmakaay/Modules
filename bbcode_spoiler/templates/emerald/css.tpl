#phorum div.bbcode_spoiler {
    border: 1px solid #999;
    background-color: #f5f5f5;
    color: black;
    padding: 5px;
    margin: 5px 0px;
}

#phorum a.bbcode_spoiler_link {
    font-size: 12px;
    padding-left: 25px;
    background-image: url({URL->HTTP_PATH}/mods/bbcode_spoiler/templates/spoiler.gif);
    background-repeat: no-repeat;
    background-position: 0px -3px;
}

#phorum a.bbcode_spoiler_link_loading {
    font-size: 12px;
    padding-left: 25px;
    background-image: url({URL->HTTP_PATH}/mods/bbcode_spoiler/templates/ajax_loading.gif);
    background-repeat: no-repeat;
    background-position: 0px 1px;
}

#phorum a.bbcode_spoiler_link_view {
    font-size: 12px;
    padding-left: 25px;
    background-image: url({URL->HTTP_PATH}/mods/bbcode_spoiler/templates/spoiler_view.gif);
    background-repeat: no-repeat;
    background-position: 0px -3px;
}

#phorum a.bbcode_spoiler_link_close {
    font-size: 12px;
    padding-left: 25px;
    background-image: url({URL->HTTP_PATH}/mods/bbcode_spoiler/templates/close.gif);
    background-repeat: no-repeat;
    background-position: 0px -3px;
    border: 0;
}

#phorum div.bbcode_spoiler_inner,
#phorum div.bbcode_spoiler_close {
    background-color: #f5f5f5;
    margin-top: 10px;
    padding: 0;
    border: 0;
}

#phorum div.bbcode_spoiler_inner {
    padding-left: 5px;
    overflow: hidden;
}
