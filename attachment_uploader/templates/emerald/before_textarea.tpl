<script type="text/javascript">
//<![CDATA[

// Override the behavior for an attach button.
function overrideAttachmentButton(attach)
{
    if (!attach) return;

    attach.onclick = function()
    {
        if (attach.name == 'attach')
        {
            // Check if we have a file upload field with a value in it.
            var inputs = this.form.getElementsByTagName('input');
            var found = false;
            for (var i = 0; i < inputs.length; i++) {
                if (inputs[i].type == 'file')
                {
                    var trimmed = inputs[i].value.replace(/^\s+|\s+$/g, '');
                    if (trimmed != '') {
                        found = true;
                        break;
                    }
                }
            }
            if (!found) {
                alert("{LANG->AttachPleaseSelectFile}");
                return false;
            }
        }

        // Create the invisible uploader iframe.
        var frame = document.getElementById('mod_attachment_uploader');
        if (!frame) {
            if (window.ActiveXObject) {
                frame = document.createElement(
                    '<iframe id="mod_attachment_uploader" ' +
                    'name="mod_attachment_uploader" />'
                );
                frame.src = 'javascript:false';
            } else {
                frame = document.createElement('iframe');
                frame.name  = 'mod_attachment_uploader';
                frame.id = 'mod_attachment_uploader';
            }

            if (0) // 1 for debug, 0 for live
            {
                frame.style.width = "500px";
                frame.style.height = "200px";
                frame.style.position = 'absolute';
                frame.style.top = '10em';
                frame.style.left = '1em';
                frame.style.zIndex = 10000;
            } else {
                frame.style.position = 'absolute';
                frame.style.top = '-500em';
                frame.style.left = '-500em';
            }

            document.body.appendChild(frame);
        }

        // Create a temporary field to notice the scripts about the
        // button that was clicked. For some reason, the buttons are
        // not passed on to the iframe submit.
        var btnname = document.getElementById('mod_attachment_uploader_b');
        if (!btnname) {
            btnname = document.createElement('input');
            btnname.id = 'mod_attachment_uploader_b';
            btnname.type = 'hidden';
            this.form.appendChild(btnname);
        }
        btnname.name = attach.name;
        btnname.value = attach.value;

        // Create a temporary field to notice the scripts that the
        // upload is done through the hidden iframe.
        var notify = document.getElementById('mod_attachment_uploader_n');
        if (!notify) {
            notify = document.createElement('input');
            notify.id = 'mod_attachment_uploader_n';
            notify.type = 'hidden';
            notify.name = 'mod_attachment_uploader';
            this.form.appendChild(notify);
        }

        // Redirect the form output to the iframe.
        notify.value = 1;
        var orig_target = this.form.target;
        this.form.target = 'mod_attachment_uploader';
        this.form.submit();
        this.form.target = orig_target;
        notify.value = 0;

        // Add an activity indicator to the page when we are uploading
        // a file. Here we simply try to put it right next to the button
        // that was clicked.
        if (attach.name == 'attach')
        {
            var image = document.createElement('img');
            image.src = '{URL->HTTP_PATH}/mods/attachment_uploader/templates/uploading.gif';
            image.style.margin = '4px 0 0 8px';
            var next = attach.nextSibling;
            if (next) {
                next.parentNode.insertBefore(image, next);
            } else {
                attach.parentNode.appendChild(image);
            }
        }

        return false;
    }
}

// This function can be used to find the posting form. The
// update_after_upload.tpl template uses this to update
// changed form fields.
function getPostingForm()
{
    // We search for the posting form by finding the form that has
    // the "Post message" button (name=finish).
    var inputs = document.getElementsByTagName('input');
    for (var i = 0; i < inputs.length; i++) {
        if (inputs[i].type == 'submit' && inputs[i].name == 'finish') {
            return inputs[i].form;
        }
    }
    return null;
}

// This function can be used to find the container in which the attachment
// management code is stored. The update_after_upload.tpl template will use
// this container to update the message form.
function getAttachmentsContainer()
{
    return document.getElementById('attachments-container');
}

// Search for the attach button(s) in the page and override their behavior.
function initAttachmentUploader()
{
  var inputs = document.getElementsByTagName('input');
  for (var i = 0; i < inputs.length; i++) {
      if ( inputs[i].type == 'submit' && (
           inputs[i].name == 'attach' ||
           inputs[i].name.match(/^detach:\d+$/)) ) {
          overrideAttachmentButton(inputs[i]);
      }
  }
}

$PJ(document).ready(initAttachmentUploader);

//]]>
</script>
