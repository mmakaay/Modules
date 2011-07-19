/**
 * This function handles initializing the template specific code for the
 * quick reply module. It needs to setup the "Quick Reply" buttons/links
 * that have to fire the quick reply editor.
 *
 * By setting up the buttons/link from javascript, you can be sure that the
 * user can actually use the quick reply editor. Since it drives on
 * javascript, it is good to do it like this.
 */
function quick_reply_init()
{
    // Find the quick reply editor.
    var editor = quick_reply_get_editor();
    if (!editor) return;

    // Add a "quick reply" button to all the messages on screen.
    // This button can be added inside the "Options:" bar.
    var divs = document.getElementsByTagName('div');
    for (var i = 0; i < divs.length; i++)
    {
        var div = divs[i];
        if (div.className && div.className == 'PhorumReadMessageBlock')
        {
            var subdivs = div.getElementsByTagName('div');
            if (subdivs.length) {
                var nav = subdivs[subdivs.length-1];
                var classname  = nav.getAttribute('class') ||
                                 nav.getAttribute('className');
                if (classname == 'PhorumReadNavBlock')
                {
                    // Create the quick reply link in the message options.
                    // This creates the link in the same way as the
                    // Emerald template.
                    var a = document.createElement('a');
                    a.className = 'PhorumNavLink';
                    a.innerHTML = '<?php print $PHORUM['DATA']['LANG']['mod_quick_reply']['QuickReply'] ?>';
                    a.onclick = function() { return quick_reply_handle(this); };
                    a.href = '#';

                    // Create the separator bullet that the classic template
                    // uses to separate message action menu items.
                    var bullet = document.createElement('span');
                    bullet.innerHTML = '&bull;';

                    // Add the new link to the start of the msg option links.
                    var ahrefs = nav.getElementsByTagName('a');
                    var nextlink = ahrefs.length ? ahrefs[0] : null;
                    nav.insertBefore(a, nextlink);
                    if (nextlink) nav.insertBefore(bullet, nextlink);
                }
            }
        }
    }
} 

/**
 * Based on the reply or quote button that was clicked, lookup the
 * information for the message to which that button belongs.
 *
 * @param Object clicked
 *     The object that was clicked to open the quick reply editor
 *     (e.g. a "Quick Reply" link).
 *
 * @return Object
 *     An object, describing the message that was clicked.
 *     It must contain at least the properties "forum_id",
 *     "thread_id" and "message_id".
 */
function quick_reply_find_message_info(clicked)
{
    // In emerald, the parent of the reply and quote links is a div
    // with the class name "PhorumReadNavBlock".
    var nav = clicked.parentNode;
    if (nav.className != 'PhorumReadNavBlock') return null;

    // Above that div, we find the "PhorumReadMessageBlock" div.
    var message = nav.parentNode;
    if (message.className != 'PhorumReadMessageBlock') return null;

    // Inside this div, there is the "PhorumReadBodyText", which holds
    // our information.
    var divs = message.getElementsByTagName('div');
    var body = null;
    for (var i = 0; i < divs.length; i++) {
        if (divs[i].className && divs[i].className == 'PhorumReadBodyText') {
            body = divs[i];
            break;
        }
    }
    if (!body) return null;

    // Inside the message body, the quick auth module has hidden some
    // info about the current message in an <a> tag. Try to fetch that tag.
    var info_tag = null;
    var atags = body.getElementsByTagName('a');
    for (var i = 0; i < atags.length; i++) {
        if (atags[i].className && atags[i].className == 'quick_reply_info') {
            info_tag = atags[i];
            break;
        }
    }
    if (!info_tag) return null;
    var info = info_tag.rel;

    // Parse the info. The info is stored in the format
    // "<forum id> <thread id> <message id>".
    var split = info.split(/ +/);
    if (split.length != 3) return null;

    // We're done. By now we should have our id's and other info available.
    return {
        'forum_id'          : split[0],
        'thread_id'         : split[1],
        'message_id'        : split[2]
    };
}

