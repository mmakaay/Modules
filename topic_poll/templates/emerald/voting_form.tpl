<form method="POST" action="{POLL->POST_URL}">
 {POST_VARS}
 <div align="center">
  <div id="topic_poll" align="left">

    <div id="topic_poll_header">
      <div id="topic_poll_title">
        {LANG->mod_topic_poll->Poll}
      </div>
      <div id="topic_poll_details">
       {IF POLL->TOTAL_VOTES_STR}{POLL->TOTAL_VOTES_STR}<br/>{/IF}
       {IF POLL->VOTING_ENDTIME}{POLL->VOTING_ENDTIME}<br/>{/IF}
       {IF POLL->PERMISSION_STR}{POLL->PERMISSION_STR}{/IF}
      </div>
    </div>

    <div id="topic_poll_question">
      {POLL->QUESTION}
    </div>

    <div id="topic_poll_answers">
      <table border="0" cellpadding="0" cellspacing="0">
        {LOOP POLL->ANSWERS}
          <tr>
            <td valign="top">
              <input type="radio" name="topic_poll:vote"
               id="topic_poll_answer_{POLL->ANSWERS->ID}"
               value="{POLL->ANSWERS->ID}" />
            </td>
            <td valign="top">
              <label for="topic_poll_answer_{POLL->ANSWERS->ID}">
                {POLL->ANSWERS->ANSWER}
              </label>
            </td>
          </tr>
        {/LOOP POLL->ANSWERS}
      </table>

      <br/>
      {IF POLL->PREVIEW}
       <input type="button" class="topic_poll_button"
        value="{LANG->mod_topic_poll->CastVote}"
        onClick="alert('{LANG->mod_topic_poll->NoVotingInPreview}')"/>
      {ELSE}
       <input type="submit" name="topic_poll:cast_vote"
        class="topic_poll_button"
        value="{LANG->mod_topic_poll->CastVote}"/>
      {/IF}

      {IF NOT POLL->PREVIEW}
       {IF POLL->CAN_VIEWRESULTS}
        <input type="submit" name="topic_poll:view_results"
         class="topic_poll_button"
         value="{LANG->mod_topic_poll->ViewResults}"/>
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
</form>

