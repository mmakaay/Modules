{! This template contains the rendering code for the attachments list. }
{! It is a straight copy from the original emerald template attachments }
{! block in the posting.tpl template. I only removed the document.write }
{! and used static code instead (we are sure that JavaScript is working }
{! for the current user anyway). }

{IF ATTACHMENTS}
    <small>{LANG->Attachments}:</small><br />
    {IF POSTING->attachments}
        <table id="attachment-list" cellspacing="0">
          {VAR LIST POSTING->attachments}
          {LOOP LIST}
            {IF LIST->keep}
              <tr>
                <td>{LIST->name} ({LIST->size})</td>
                <td align="right">
                  {HOOK "tpl_editor_attachment_buttons" LIST}
                  <input type="submit" name="detach:{LIST->file_id}" value="{LANG->Detach}" />
                </td>
              </tr>
            {/IF}
          {/LOOP LIST}
        </table>
        {VAR AttachPhrase LANG->AttachAnotherFile}
    {ELSE}
        {VAR AttachPhrase LANG->AttachAFile}
    {/IF}

    {IF ATTACHMENTS_FULL}
        <strong>{LANG->AttachFull}</strong>
        <br/>
    {ELSE}
        <script type="text/javascript">
            function phorumShowAttachForm() {
              document.getElementById('attach-link').style.display='none';
              document.getElementById('attach-form').style.display='block';
            }
        </script>
        <div id="attach-link" class="attach-link" style="display: block">
           <a href="javascript:phorumShowAttachForm()">
              <b>{AttachPhrase} ...</b>
           </a>
        </div>
        <div id="attach-form" style="display: none">
          <div class="attach-link">{AttachPhrase}</div>
          <ul>
            {IF EXPLAIN_ATTACH_FILE_TYPES}<li>{EXPLAIN_ATTACH_FILE_TYPES}</li>{/IF}
            {IF EXPLAIN_ATTACH_FILE_SIZE}<li>{EXPLAIN_ATTACH_FILE_SIZE}</li>{/IF}
            {IF EXPLAIN_ATTACH_TOTALFILE_SIZE}<li>{EXPLAIN_ATTACH_TOTALFILE_SIZE}</li>{/IF}
            {IF EXPLAIN_ATTACH_MAX_ATTACHMENTS}<li>{EXPLAIN_ATTACH_MAX_ATTACHMENTS}</li>{/IF}
          </ul>
          <input type="file" size="50" name="attachment" />
          <input type="submit" name="attach" value="{LANG->Attach}" />
        </div>
    {/IF}
    <br/>
{/IF}
