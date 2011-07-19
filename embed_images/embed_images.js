function mod_embed_images_loadimage(
    viewer_id, thumbnail_url, fullimage_url, ajax_url, target_url,
    message_id, max_w, max_h, loading_txt, rescheduled)
{
    var container = document.getElementById('imagediv_'+viewer_id);
    if (!container) return;

    // If the image for the viewer id is already on the page, then we are
    // handling a cached thumbnail for which no Ajax magic is required,
    // but which is already loaded in the page. We'll only use this function
    // for setting up a full size image viewer.
    var image = document.getElementById('image_'+viewer_id);
    if (image)
    {
        // If a function was loaded for a viewer to initialize the
        // viewer, then hand over the image data to that function.
        // Don't do this in case we have a target_url.
        if (window.mod_embed_images_initviewer && !target_url) {
            var link  = document.getElementById('link_'+viewer_id);
            mod_embed_images_initviewer(
                container, image, link, fullimage_url, message_id
            );
        }

        return;
    }

    if (!rescheduled)
    {
        // We use a bit of a strange hack here, by checking for readyState.
        // This is not availble in all browsers. That's no problem though.
        // We mainly want MSIE to check it and to let it postpone image
        // loading till after the page is fully loaded. When doing things
        // earlier in MSIE from script code that is not a direct descendant
        // of <body>, we might get "operation aborted" errors.
        if (document.readyState &&
            document.readyState != 'loaded' &&
            document.readyState != 'complete' &&
            window.attachEvent &&
            !window.opera) {
          window.attachEvent('onload', function() {
              mod_embed_images_loadimage(
                  viewer_id, thumbnail_url, fullimage_url,
                  ajax_url, target_url, message_id,
                  max_w, max_h, loading_txt, true
              );
          });
          return;
        }

        // Display a "Loading ..." message to the user.
        container.innerHTML =
            '<div class="mod_embed_images_loading">' +
            loading_txt +
            '<\/div>';
    }

    // Create the XMLHttpRequest object that we can use to send an
    // Ajax request to the server.
    var xhr;
    if (window.XMLHttpRequest) {
        xhr = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        var versions = [
            "MSXML2.XMLHttp.5.0", "MSXML2.XMLHttp.4.0",
            "MSXML2.XMLHttp.3.0", "MSXML2.XMLHttp",
            "Microsoft.XMLHttp"
        ];
        for (var i=0; i < versions.length; i++)
          try { xhr = new ActiveXObject(versions[i]); } catch (e) { }
    }

    // No XMLHttpRequest object found? Fallback to a simpler way of
    // displaying the thumbnail image. This way we won't have
    // loading or error feedback and full size viewer support,
    // but at least we do show the image. This should't happen for
    // modern browsers.
    if (!xhr)
    {
        target.innerHTML =
            (target_url ? '<a href="'+target_url+'">' : '') +
            '<img id="image_'+viewer_id+'" src="'+thumbnail_url+'"/>' +
            (target_url ? '<\/a>' : '');
        return;
    }

    // Setup the XMLHttpRequest object for the request.
    xhr.open("get", ajax_url, true);
    xhr.setRequestHeader("Content-Type", "text/plain");
    xhr.onreadystatechange = function()
    {
        if (xhr.readyState == 4 && xhr.status == 200)
        {
            var res = xhr.responseText;

            // An "OK <w>x<h> <scw>x<sch>" message was returned.
            if (res.substr(0,2) == 'OK')
            {
                // Parse the respones message.
                // I know... a bit old school parsing.
                var dim   = res.substr(3);
                var xpos  = dim.indexOf('x');
                var spos  = dim.indexOf(' ');
                var origw = dim.substr(0,xpos);
                var origh = dim.substr(xpos+1, spos-xpos-1);
                    dim   = dim.substr(spos+1);
                    xpos  = dim.indexOf('x');
                var w     = dim.substr(0,xpos);
                var h     = dim.substr(xpos+1);

                var origsize  = origw+"x"+origh;
                var scalesize = w+"x"+h;
                var is_scaled = (origsize != scalesize);

                // Opera does not seem to load images that are not visible :(
                // So for those, we never get an onload event. Therefore,
                // we make the image 0x0 pixels and resize it to the real
                // size after loading.
                var html =
                    (target_url ? '<a href="'+target_url+'">' : '') +
                    '<img style="width:0px;height:0px" ' +
                    'id="image_'+viewer_id+'" ' +
                    'onload="' +
                    'mod_embed_images_image_loaded(this,'+w+','+h+')" ' +
                    'src="'+thumbnail_url+'"/>' +
                    (target_url ? '<\/a>' : '');

                container.innerHTML += html;

                // Go to extended viewer mode if the image was scaled down
                // or if a target URL was provided.
                if (is_scaled || target_url)
                {
                    var info = document.getElementById('info_'+viewer_id);
                    if (info) info.style.display = 'block';

                    var div = document.getElementById('div_'+viewer_id);
                    if (div) div.className = 'mod_embed_images_extended';
                }

                // If a function was loaded for a viewer to initialize the
                // viewer, then hand over the image data to that function.
                if (window.mod_embed_images_initviewer && !target_url) {
                    var image = document.getElementById('image_'+viewer_id);
                    var link  = document.getElementById('link_'+viewer_id);
                    mod_embed_images_initviewer(
                        container, image, link, fullimage_url,
                        message_id
                    );
                }
            }

            // Some error message was returned. Show the error to the user.
            else container.innerHTML =
                '<div class="mod_embed_images_error">' +
                '<strong>Image error<\/strong><br\/>' +
                res + '<br/>' +
                '<a href="' + fullimage_url + '">open image URL<\/a>' +
                '<\/div>';
        }
    }

    // Send the request to the server.
    xhr.send('');
}

function mod_embed_images_image_loaded(image, w, h)
{
    image.style.width = w+'px';
    image.style.height = h+'px';

    var container = image.parentNode;
    while (container.className != 'mod_embed_images_image') {
        container = container.parentNode;
        if (!container) return; // Should not happen.
    }

    container.style.width = image.width + 'px';
    container.parentNode.style.width = parseInt(image.width) + 'px';
    container.style.height = image.height + 'px';
    for (var i = 0; i < container.childNodes.length; i++) {
        if (container.childNodes[i].className == 'mod_embed_images_loading') {
            container.childNodes[i].style.display = 'none';
        }
    }
}

