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
    a.onclick = function () {
        myLightbox.start(this);
        return false;
    }
    a.appendChild(image);

    if (link) {
        link.rel = 'lightbox['+message_id+']';
        link.onclick = function () {
            myLightbox.start(this);
            return false;
        }
    }
}

