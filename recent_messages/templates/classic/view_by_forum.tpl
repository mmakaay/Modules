{VAR DISPLAY_FORUM FALSE}

{LOOP FOLDERS}

  <tr>
    <td style="background-color: white; color:black" colspan="3">
      <strong>
        &#187; {FOLDERS->path_name}
      </strong>
    </td>
  </tr>

  {LOOP FOLDERS->MESSAGES}
    {VAR MESSAGE FOLDERS->MESSAGES}
    {INCLUDE "recent_messages::render_message_row"}
  {/LOOP FOLDERS->MESSAGES}

{/LOOP FOLDERS}

