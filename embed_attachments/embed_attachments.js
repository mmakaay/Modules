function mod_embed_attachments_create_link(id,fn)
{
    // Find the textarea. Multiple cases for backward compatibility.
    var area = document.getElementById("phorum_textarea");
    if (! area) area = document.getElementById("body");
    if (! area) {
        alert(
          'There seems to be a technical problem. The textarea cannot be ' +
          'found in the page. The textarea should have id="body" in the ' +
          'definition, so the Embed Attachments module can find it.'
        );
        return
    }

    // Add attachment BBcode to the message body.
    fn = unescape(fn);
    var attlink = '[attachment ' + id + ' ' + fn + ']';

    if (area.createTextRange) /* MSIE */
    {
        area.focus(area.caretPos);
        area.caretPos = document.selection.createRange().duplicate();
        curtxt = area.caretPos.text;
        area.caretPos.text = attlink + curtxt;
    }
    else /* Other browsers */
    {
        var pos = area.selectionStart;
        area.value =
            area.value.substring(0,pos) +
            attlink +
            area.value.substring(pos);
        area.focus();
        area.selectionStart = pos + attlink.length;
        area.selectionEnd = area.selectionStart;
    }
}

