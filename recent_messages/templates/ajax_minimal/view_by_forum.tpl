{VAR DISPLAY_FORUM FALSE}

{LOOP FOLDERS}

  <div class="prm-separator">
    &#187; {FOLDERS->path_name}
  </div>

  {LOOP FOLDERS->MESSAGES}
    {VAR MESSAGE FOLDERS->MESSAGES}
    {INCLUDE "recent_messages::render_message_row_ajax"}
  {/LOOP FOLDERS->MESSAGES}

{/LOOP FOLDERS}
