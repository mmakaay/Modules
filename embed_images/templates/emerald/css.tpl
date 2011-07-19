/* The style to use for standard images */
#phorum div.mod_embed_images {
    float: left;
    margin-right: 10px;
    margin-bottom: 10px;
    font-size: 10px;
}

/* Extended is used in case an image is scaled down or inside a URL */
#phorum div.mod_embed_images_extended {
    float: left;
    margin-right: 10px;
    margin-bottom: 10px;
    font-size: 10px;
    border: 1px solid #b0b0b0;
    background-color: #f5f5f5;
}

#phorum div.mod_embed_images_image {
}

#phorum div.mod_embed_images_image a,
#phorum div.mod_embed_images_info a {
    text-decoration: none;
    color: black;
}

#phorum div.mod_embed_images_image a:hover,
#phorum div.mod_embed_images_info a:hover {
    text-decoration: underline;
}

#phorum div.mod_embed_images_loading {
    font-size: 10px;
    padding: 5px 5px 3px 22px;
    height: 16px;
    background-position: 3px 3px;
    background-repeat: no-repeat;
    background-image: url({URL->HTTP_PATH}/mods/embed_images/templates/ajax_loading.gif);
}

#phorum div.mod_embed_images_info {
    border-top: 1px solid #b0b0b0;
    height: 18px;
    padding: 3px 0px 0px 21px;
    white-space: nowrap;
    overflow: hidden;
    background-position: 3px 5px;
    background-repeat: no-repeat;
    background-image: url({URL->HTTP_PATH}/mods/embed_images/templates/magnify_icon.gif);

    /* Will be made visible if extended viewing is triggered */
    display: none;
}

#phorum div.mod_embed_images_info_link
{
    padding-left: 22px;
    background-image: url({URL->HTTP_PATH}/mods/embed_images/templates/link_icon.png);
}

#phorum div.mod_embed_images_error
{
    color: #a00000;
    border: 1px solid #a00000;
    padding: 5px 5px 5px 43px;
    background-image: url({URL->HTTP_PATH}/mods/embed_images/templates/broken_image.gif);
    background-position: 5px 5px;
    background-repeat: no-repeat;
}

#phorum div.mod_embed_images_error strong
{
    font-size: 12px;
}

#phorum div.mod_embed_images_attachments
{
    margin-top: 20px;
}

/* Should be in the main Phorum CSS too, but let's make sure. */
#phorum div.message-body br {
    clear: both;
}

