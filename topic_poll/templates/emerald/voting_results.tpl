{! Prevent nested forms in the admin interface }
{IF NOT POLL->ADMIN_PREVIEW}
  <form method="POST" action="{POLL->POST_URL}">
  {POST_VARS}
{/IF}
<div align="center">
 <div id="topic_poll" align="left" class="topic_poll_results">

    <div id="topic_poll_header">
      <div id="topic_poll_title">
        {LANG->mod_topic_poll->Poll}
      </div>
    </div>

    <div id="topic_poll_question">
      <?php print htmlspecialchars($poll["question"]) ?>
    </div>

    <div id="topic_poll_answers">

      {IF POLL->STATUSMESSAGE}
        <div id="topic_poll_statusmessage">
          {POLL->STATUSMESSAGE}
        </div>
      {/IF}

      <table border="0" cellpadding="0" cellspacing="0" width="95%">

        {LOOP POLL->ANSWERS}
          <tr>
            <td valign="middle" id="topic_poll_answer_column">
              {IF POLL->ANSWERS->ID POLL->CURRENT_VOTE}
                <b>{POLL->ANSWERS->ANSWER}</b>
              {ELSE}
                {POLL->ANSWERS->ANSWER}
              {/IF}
            </td>
            <td valign="middle" align="right" id="topic_poll_votes_column">
              {POLL->ANSWERS->VOTES}
            </td>
            <td width="60%" valign="middle" id="topic_poll_bar_column">
              <div class="topic_poll_percentage_bar"
               style="width:{POLL->ANSWERS->BARWIDTH}">&nbsp;</div>
            </td>
            <td valign="middle" align="right"
             class="topic_poll_percentage_column">
              {POLL->ANSWERS->PERCENTAGE}
            </td>
          </tr>
        {/LOOP POLL->ANSWERS}

      </table>

      {IF POLL->CAN_REVOKE}
        <br/>
        <input type="submit" name="topic_poll:revoke_vote"
         class="topic_poll_button"
         value="{LANG->mod_topic_poll->RevokeVote}" />
      {ELSE}
        {IF POLL->CAN_VOTE}
          <br/>
          <input type="submit"
           class="topic_poll_button"
           value="{LANG->mod_topic_poll->GotoVote}" />
        {/IF}
      {/IF}

      {IF POLL->NOVOTENOREAD}
        <div id="topic_poll_novotenoread">
          {POLL->NOVOTENOREAD}
        </div>
      {/IF}

    </div>
  </div> <!-- topic_poll div -->
</div> <!-- center div -->
{IF NOT POLL->ADMIN_PREVIEW}
  </form>
{/IF}
