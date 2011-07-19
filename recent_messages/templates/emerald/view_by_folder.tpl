{VAR DISPLAY_FORUM TRUE}

{LOOP FOLDERS}

  {VAR GROUP_HEADING FOLDERS->path_name}
  {INCLUDE "recent_messages::render_group_row"}

  {LOOP FOLDERS->MESSAGES}
    {VAR MESSAGE FOLDERS->MESSAGES}
    {INCLUDE "recent_messages::render_message_row"}
  {/LOOP FOLDERS->MESSAGES}

{/LOOP FOLDERS}
