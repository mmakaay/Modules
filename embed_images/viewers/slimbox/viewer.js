// We do not use the lightbox init code for setting up the thumbnails.
// Instead, we setup the required events and data ourselves for each image,
// right after it is loaded. This is useful in case we dynamically add images
// to the page (e.g. through spoilers) and for making the image viewer act
// on image clicks as soon as possible.

function mod_embed_images_initviewer(container, image, link, url, message_id)
{
    var a = document.createElement('a');
    a.href = url;
    container.appendChild(a);
    a.rel = 'lightbox['+message_id+']';
    a.appendChild(image);

    a.onclick = Lightbox.click.pass(a, Lightbox);
    if (Lightbox.anchors) {
        Lightbox.anchors.push(a);
    }

    if (link) {
        link.onclick = Lightbox.click.pass(link, Lightbox);
        if (Lightbox.anchors) {
            Lightbox.anchors.push(link);
        }
    }
}

