<div class="attachments">
  <strong>{LANG->mod_topic_poll->Poll}</strong>:
  {POLL->QUESTION}
  {IF POLL->STATUSMESSAGE}
  <div style="font-size: 12px; padding-top: 5px">
    {POLL->STATUSMESSAGE}
  </div>
  {/IF}
  <div style="font-size: 12px; padding-top: 5px">
    {IF POLL->CAN_EDIT}
      <input type="submit" name="topic_poll:edit"
       style="font-size: 10px"
       value="{LANG->mod_topic_poll->EditPoll}" />
    {/IF}
    {IF POLL->CAN_SETSTATUS}
      {IF POLL->ACTIVE}
        <input type="submit" name="topic_poll:deactivate"
         style="font-size: 10px"
         value="{LANG->mod_topic_poll->DeactivatePoll}" />
      {ELSE}
        <input type="submit" name="topic_poll:activate"
         style="font-size: 10px"
         value="{LANG->mod_topic_poll->ActivatePoll}" />
      {/IF}
    {/IF}
    {IF POLL->CAN_DELETE}
      <input type="submit" name="topic_poll:delete"
       style="font-size: 10px"
       onClick="return confirm('{LANG->AreYouSure}')"
       value="{LANG->mod_topic_poll->DeletePoll}" />
    {/IF}
  </div>
</div>
