// ----------------------------------------------------------------------
// Initialization code for the hide reply editor module.
// ----------------------------------------------------------------------

$PJ(document).ready(function ()
{
    // Find the hide reply editor wrapper.
    var $editor_wrapper = $PJ('#mod_hide_reply_editor');
    if ($editor_wrapper.length == 0) return;

    // Hide the editor in browsers that do support JavaScript.
    // If we see #REPLY_SHOWEDITOR in the URL, then the user already clicked
    // a Reply or Quote link (the page could be reloaded after such click,
    // so we should not be hiding the editor).
    if (!document.location.href.match(/#REPLY_SHOWEDITOR/)) {
        $editor_wrapper.hide();
    }

    // Add events to the "Reply" and "Quote" links to show the editor.
    // The editor is shown after the click, in case the bare URL (without
    // the #ANCHOR) of the clicked link matches the current page URL.
    // If that is the case, then showing the reply editor is barely a
    // matter of the browser jumping to the #REPLY_SHOWEDITOR anchor.
    // Otherwise, the browser will simply jump to the new URL.
    //
    // Additionally, the anchor hash from the reply and quote URLs is
    // updated to #REPLY_SHOWEDITOR to indicate the above code that one
    // of these links was clicked and that the editor should not be hidden.
    //
    $PJ('a').each(function (dummy, anchor)
    {
        if (anchor.href && anchor.href.match(/#REPLY$/))
        {
            anchor.href += '_SHOWEDITOR';
            $PJ(anchor).click(function()
            {
                var pos;

                var url1 = document.location.href;
                if ((pos = url1.indexOf('#')) > 0) {
                    url1 = url1.substr(0, pos);
                }

                var url2 = this.href;
                if ((pos = url2.indexOf('#')) > 0) {
                    url2 = url2.substr(0, pos);
                }

                if (url1 == url2) {
                    $editor_wrapper.show();
                    $editor_wrapper.find('textarea').focus();
                }
            });
        }
    });

    // When loading a page, focus the editor when #REPLY_SHOWEDITOR is
    // used in the URL.
    if (document.location.href.match(/#REPLY_SHOWEDITOR$/)) {
        $editor_wrapper.find('textarea').focus();
    }
});

