// Settings for the FancyBox as used in Phorum. When other options are
// needed, then these can be overridden.
// For an overview of the possible settings, see http://fancybox.net/api
var phorum_fancybox_settings = {
    type               : 'image',
    titlePosition      : 'inside',
    autoScale          : true,
    margin             : 30,
    transitionIn       : 'elastic',
    transitionOut      : 'elastic',
    hideOnOverlayClick : true,
    hideOnContentClick : true
};

function mod_embed_images_initviewer(container, image, link, url, message_id)
{
    var a = document.createElement('a');
    a.href = url;
    a.rel = 'fancybox_' + message_id;
    container.appendChild(a);
    a.appendChild(image);
    $PJ(a).fancybox(phorum_fancybox_settings);

    if (link) {
        $PJ(link).fancybox(phorum_fancybox_settings);
    }
}

