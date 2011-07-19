function mod_embed_images_initviewer(container, image, link, url, message_id)
{
    var a = document.createElement('a');
    a.href = url;
    a.target = '_new';
    container.appendChild(a);
    a.appendChild(image);
}

