{IF ERROR}<div class="attention">{ERROR}</div>{/IF}
{IF OKMSG}<div class="information">{OKMSG}</div>{/IF}

<form id="post_form" name="post" action="{URL->ACTION}" method="post"
 enctype="multipart/form-data">
{POST_VARS}

<div class="generic" style="font-size: 12px">

  <table border="0" cellpadding="4" cellspacing="0">

  <tr>
    <td colspan="2" style="padding-bottom: 15px">
      <h1>{LANG->mod_topic_poll->EditorTitle}</h1>
    </td>
  </tr>

  <tr>
    <td>{LANG->mod_topic_poll->Question}</td>
    <td>
      <input type="text" name="topic_poll:question"
       value="{POLL->QUESTION}" size="50" />
    </td>
  </tr>

  {LOOP POLL->ANSWERS}
  <tr>
    <td>{LANG->mod_topic_poll->Answer} {POLL->ANSWERS->NUMBER}</td>
    <td>
      <input type="text" name="topic_poll:answer:{POLL->ANSWERS->ID}"
       value="{POLL->ANSWERS->ANSWER}" size="50" />
      {IF POLL->CAN_DELETE_ANSWERS}
      <input type="submit" value="{LANG->mod_topic_poll->DeleteAnswer}"
       name="topic_poll:delete_answer:{POLL->ANSWERS->ID}" />
      {/IF}
    </td>
  </tr>
  {/LOOP POLL->ANSWERS}

  <tr>
    <td></td>
    <td>
      <input type="submit" name="topic_poll:add_answer"
       value="{LANG->mod_topic_poll->AddAnswer}" />
    </td>
  </tr>

  <tr>
    <td colspan="2" style="padding-top: 15px; padding-bottom: 15px">
      <h1>{LANG->mod_topic_poll->PollSettings}</h1>
    </td>
  </tr>

  <tr>
    <td colspan="2">
      <select name="topic_poll:permission">
        <option value="user"
         {IF POLL->PERMISSION "user"}selected="selected"{/IF}>
         {LANG->mod_topic_poll->PermissionUser}
        </option>
        <option value="anonymous"
         {IF POLL->PERMISSION "anonymous"}selected="selected"{/IF}>
         {LANG->mod_topic_poll->PermissionAnonymous}
        </option>
      </select><br/>
      <select name="topic_poll:novotenoread" style="margin-top: 0.5em">
        <option value="0">
         {LANG->mod_topic_poll->NoVoteReadAllow}
        </option>
        <option value="1"
         {IF POLL->NOVOTENOREAD}selected="selected"{/IF}>
         {LANG->mod_topic_poll->NoVoteReadDeny}
        </option>
      </select>
    </td>
  </tr>

  <tr>
    <td colspan="2">
      {LANG->mod_topic_poll->TimeToVotePre}
      <input type="text" name="topic_poll:votingtime" size="4"
       value="{POLL->VOTINGTIME}" /> {LANG->mod_topic_poll->TimeToVotePost}
    </td>
  </tr>

</table>
<br/>

</div>

<div id="post-buttons">
  <input type="submit"
         name="topic_poll:back_to_message"
         value="{LANG->mod_topic_poll->BackToMessage}"/>
  {IF POLL->CAN_DELETE}
  <input type="submit" value="{LANG->mod_topic_poll->DeletePoll}"
         name="topic_poll:delete"
         onClick="return confirm('{LANG->AreYouSure}')"/>
  {/IF}
</div>

</form>
