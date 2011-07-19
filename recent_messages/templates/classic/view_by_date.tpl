{VAR DISPLAY_FORUM TRUE}

{VAR PREVDATE "bigbang"}

{LOOP MESSAGES}

  {VAR MESSAGE MESSAGES}
  {VAR MESSAGE->datestamp MESSAGE->time}
  {VAR MESSAGE->lastpost  MESSAGE->time}

  {IF NOT PREVDATE MESSAGE->date}
  <tr>
    <td style="background-color: white; color:black" colspan="4">
      <strong>
        &#187; {MESSAGE->date}
      </strong>
    </td>
  </tr>
  {VAR PREVDATE MESSAGE->date}
  {/IF}

  {INCLUDE "recent_messages::render_message_row"}

{/LOOP MESSAGES}
