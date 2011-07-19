// ----------------------------------------------------------------------
// Functions for the Quick Reply module.
//
// The default implementation of these functions will work with the
// default Emerald template. For other templates, a different
// implementation could be required. Those templates can implement
// functions in mods/quick_reply/templates/<template>/javascript.tpl
// to override the default implementations.
// ----------------------------------------------------------------------

/**
 * This function handles initializing the template specific code for the
 * quick reply module. It needs to setup the "Quick Reply" buttons/links
 * that have to fire the quick reply editor.
 *
 * By setting up the buttons/link from javascript, you can be sure that the
 * user can actually use the quick reply editor. Since it drives on
 * javascript, it is good to do it like this.
 *
 * The default implementation will add an Emerald template style
 * "Quick Reply" button to the button bar that normally says
 * "Reply Quote Report". Each message has this button bar in a <div>
 * with id="message-options". 
 */
function quick_reply_init()
{
    // Find the quick reply editor.
    var editor = quick_reply_get_editor();
    if (!editor) return;

    // Add a "quick reply" button to all the messages on screen.
    var divs = document.getElementsByTagName('div');
    for (var i = 0; i < divs.length; i++)
    {
        var div = divs[i];
        var classname = div.getAttribute('class') ||
                        div.getAttribute('className');

        if (classname && classname == 'message-options')
        {
            // Create the quick reply link in the message options.
            // This creates the link in the same way as the Emerald template.
            var a = document.createElement('a');
            a.className = 'icon icon-comment-add';
            a.innerHTML = '<?php print $PHORUM['DATA']['LANG']['mod_quick_reply']['QuickReply'] ?>';
            a.onclick = function() { return quick_reply_handle(this); };
            a.href = '#';

            // Add the new link to the start of the message-option links.
            div.insertBefore(a, div.firstChild);
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
    // with the class name "message-options".
    var message_options = clicked.parentNode;
    if (message_options.className != 'message-options') return null;

    // Above that div, we find the "message-body" div.
    var message_body = message_options.parentNode;
    if (message_body.className != 'message-body') return null;

    // Inside the message body, the quick auth module has hidden some
    // info about the current message in an <a> tag. Try to fetch that tag.
    var info_tag = null;
    var atags = message_body.getElementsByTagName('a');
    for (var i = 0; i < atags.length; i++) {
        var classname = atags[i].getAttribute('class') ||
                        atags[i].getAttribute('className');
        if (classname && classname == 'quick_reply_info') {
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

// ----------------------------------------------------------------------
// Event handling functions
// ----------------------------------------------------------------------

/**
 * Handle a click on a quick reply button/link.
 *
 * This function is called when the user clicks on "Quick Reply".
 * It will setup the quick reply editor form with fields to make it a
 * valid reply form for Phorum and it will show and position the quick
 * reply editor on the screen.
 *
 * @return Boolean false
 *     So onclick="return quick_reply_handle()" can be used to prevent
 *     the button from submitting the form.
 */
function quick_reply_handle(clicked)
{
    var info = quick_reply_find_message_info(clicked); 
    if (info == null) return quick_reply_error('unable to find message info');

    var editor = quick_reply_get_editor();

    // Return if the editor is already open for the requested message.
    if (editor.message_id && editor.message_id == info.message_id) {
        return false;
    }

    Phorum.Ajax.call({
        'call'       : 'quick_reply_init_editor',
	'store'      : info.message_id, // Ajax cache Phorum 5.2
        'cache_id'   : info.message_id, // Ajax cache Phorum 5.3+
        'message_id' : info.message_id,
        'forum_id'   : info.forum_id,
        'onFailure'  : function(data)
        {
            alert(data);
        },
        'onSuccess'  : function(data)
        {
            // Hide the editor.
            editor.style.display = 'none';

            // Update the POST variables.
            var p = document.getElementById('quick_post_vars');
            if (!p) return quick_reply_error('unable to find #quick_post_vars');
            p.innerHTML = data.post_vars;

            // Add spam hurdles code.
            var sh = document.getElementById('quick_spam_hurdles');
            if (!sh) return quick_reply_error(
                'unable to find #quick_spam_hurldes'
            );
            sh.innerHTML = data.spam_hurdles;
            Phorum.Ajax.evalJavaScript(data.spam_hurdles);

            // Update the subject text entry.
            var s = document.getElementById('quick_subject');
            if (!s) return quick_reply_error('unable to find #quick_subject');
            s.value = data.subject;

            // Update the form action parameter.
            var f = document.getElementById('quick_form');
            if (!f) return quick_reply_error('unable to find #quick_form');
            f.action = data.url;

            // Render the editor outside the screen so we can measure its size.
            var pos = quick_reply_get_screenpos(clicked);
            editor.style.top = '-100em';
            editor.style.left = '-100em';
            editor.style.display = 'block';

            // Determine where to show the editor.
            var left = pos.left + clicked.offsetWidth - editor.offsetWidth;
            if (left < 10) left = 10;
            var top  = pos.top + clicked.offsetHeight;
            var ci = quick_reply_get_clientinfo();
            if ((top + editor.offsetHeight) > (ci[0] + ci[1])) {
                top = ci[0] + ci[1] - editor.offsetHeight;
                if (top < 0) top = 0;
            }

            // Shift the editor into place.
            editor.style.left = left + 'px';
            editor.style.top =  top + 'px';

            // Set the id for the active quick reply link, so we can use
            // that one to style the link.
            var link = document.getElementById('mod_quick_reply_active_link');
            if (link) link.id = null;

            clicked.id = 'mod_quick_reply_active_link';

            // Focus the textarea.
            var t = document.getElementById('quick_body');
            if (t) t.focus();

            // Keep track in the editor for which message id it was opened.
            editor.message_id = info.message_id;
        }
    });

    return false;
}

/**
 * The user clicks "Cancel" in the editor.
 *
 * This is the function that is called by the default quick reply editor
 * template when the user clicks the "Cancel" button. It will hide the
 * #mod_quick_reply div and remove the id #mod_quick_reply_active_link
 * from the active quick reply link.
 *
 * @return Boolean false
 *     So onclick="return quick_reply_cancel()" can be used to prevent
 *     the button from submitting the form.
 */
function quick_reply_cancel()
{
    var editor = quick_reply_get_editor();
    editor.style.display = 'none';
    editor.message_id = null;

    var link = document.getElementById('mod_quick_reply_active_link');
    if (link) link.id = null;

    return false;
}

// ----------------------------------------------------------------------
// Helper functions
// ----------------------------------------------------------------------

/**
 * Returns the message editor wrapper object.
 *
 * This one possibly could require an override, but it is generally
 * recommended to wrap the quick reply editor in a div with
 * id="mod_quick_reply" and keep this function the same.
 *
 * @return object
 *     The quick reply editor wrapper.
 */
function quick_reply_get_editor()
{
    return document.getElementById('mod_quick_reply');
}

/**
 * A helper function that we can use in case something goes wrong.
 *
 * @param String msg
 *     The error message to show to the user.
 */
function quick_reply_error(msg)
{
    alert('There was an error in the Quick Reply Module. Please inform ' +
          'the module author about this error: ' + msg);
    return false;
}

/**
 * A helper function for finding the available client height and the
 * document scroll offset.
 *
 * @return Array
 *     An array containing two elements:
 *     - The available client height or null if the client height
 *       could not be determined;
 *     - The current scroll offset or null if the scroll offset
 *       could not be determined.
 */
function quick_reply_get_clientinfo()
{
    var heights = [
        window.innerHeight ? window.innerHeight : null,
        document.documentElement ? document.documentElement.clientHeight : null,
        document.body ? document.body.clientHeight : null
    ];

    var height = null;
    for (var i = 0; i < heights.length; i++) {
        if (height == null || (heights[i] && heights[i] < height)) {
            height = heights[i];
        }
    }

    var scroll = null;
    if (document.body.scrollTop) {
      scroll = document.body.scrollTop;
    } else if (document.documentElement.scrollTop) {
      scroll = document.documentElement.scrollTop
    } else if (window.pageYOffset) {
      scroll = window.pageYOffset;
    }

    return [ height, scroll ];
}

/**
 * A helper function for finding the position of an element on screen.
 *
 * @param Object obj
 *     The object to determine the location for.
 *
 * @return Object
 *     An object containing the properties "top" and "left", containing
 *     the absolute pixel offsets of the provided object.
 */
function quick_reply_get_screenpos(obj)
{
    var top = left = 0;

    do {
        top  += obj.offsetTop;
        left += obj.offsetLeft;
        obj = obj.offsetParent;
    } while (obj);

    return {
        'top'      : top,
        'left'     : left
    };
}

