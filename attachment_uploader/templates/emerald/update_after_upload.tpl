<?php if ($PHORUM['DATA']['CHARSET']) {
    header("Content-Type: text/html; charset=".htmlspecialchars($PHORUM['DATA']['CHARSET']));
} ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{LOCALE}" lang="{LOCALE}">
<head><title>File upload result</title></head>
<body style="background-color: white">

<form id="attachments-html" method="post" target="">
  {INCLUDE "attachment_uploader::attachments"}
</form>

<form id="attachments-form" action="/" method="post">
  {POST_VARS}
</form>

{IF ERROR}
<form id="attachments-error" method="post" target="">{ERROR}</form>
{/IF}

<script type="text/javascript">
//<![CDATA[

// Target and source for the attachment form code.
var tgt = parent.getAttachmentsContainer();
var src = document.getElementById('attachments-html');

// Target and source for the posting form fields.
var tgtf = parent.getPostingForm();
var srcf = document.getElementById('attachments-form');

// If all is okay, then copy data to the main posting form.
if (tgt && src && tgtf && srcf)
{
    // Copy attachments HTML code for the form.
    tgt.innerHTML = src.innerHTML;

    // Copy form fields that relate to attachments.
    // These fields are the primary attachments fields.
    tgtf['attachments'].value = srcf['attachments'].value;
    tgtf['attachments:signature'].value = srcf['attachments:signature'].value;

    // Possibly, modules could add data to the meta data
    // array. Let's be prepared for that.
    tgtf['meta'].value = srcf['meta'].value;
    tgtf['meta:signature'].value = srcf['meta:signature'].value;

    // If there was an error, then show it to the user.
    var err = document.getElementById('attachments-error');
    if (err) { alert(err.innerHTML); }

    // Reinitialize the attachment button override in the main form.
    parent.initAttachmentUploader();
}

//]]>
</script>

</table>

</body>
</html>
