function mod_embed_images_initviewer(container, image, link, url, message_id)
{
    var imgpath = Phorum.http_path +
                  '/mods/embed_images/viewers/jquery_lightbox/code/images/';

    var settings = {
        imageLoading  : imgpath + 'lightbox-ico-loading.gif',
        imageBtnPrev  : imgpath + 'lightbox-btn-prev.gif',
        imageBtnNext  : imgpath + 'lightbox-btn-next.gif',
        imageBtnClose : imgpath + 'lightbox-btn-close.gif',
        imageBlank    : imgpath + 'lightbox-blank.gif',
    };

    var a = document.createElement('a');
    a.href = url;
    container.appendChild(a);
    a.appendChild(image);
    $PJ(a).lightBox(settings);

    if (link) {
        $PJ(link).lightBox(settings);
    }
}

