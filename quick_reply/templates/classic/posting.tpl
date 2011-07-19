<div id="mod_quick_reply">
  <form id="quick_form" name="post_form" action="" method="post"
        enctype="multipart/form-data">
    <div id="quick_post_vars"></div>
    <input type="hidden" name="is_quick_reply" value="1"/>

    <table>

      <tr>
        <th colspan="2">{LANG->mod_quick_reply->SendQuickReply}</th>
      </tr>

      <tr class="quick_subject">
        <td>
          <label for="quick_subject">
            {LANG->Subject}:
          </label>
        </td>
        <td>
          <input type="text" name="subject" id="quick_subject"
                 size="50" value="" />
        </td>
      </tr>

      <tr class="quick_body">
        <td>
          <label for="quick_body">
            {LANG->Message}:
          </label>
        </td>
        <td>
          <fieldset>
            <textarea name="body" id="quick_body"
                      rows="5" cols="58"></textarea>
          </fieldset>

          <div id="quick_spam_hurdles"/>
        </td>
      </tr>

      <tr class="quick_buttons">
        <td>
        </td>
        <td>
          <input type="submit" name="preview" value=" {LANG->Preview} " />
          <input type="submit" name="finish" value=" {LANG->Post} " />
          <input type="submit" name="cancel" value=" {LANG->Cancel} " 
                 onclick="return quick_reply_cancel()"/>
        </td>
      </tr>

    </table>

  </form>
</div>


<script type="text/javascript">
  quick_reply_init();
</script>
