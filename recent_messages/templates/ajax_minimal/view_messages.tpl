{VAR DISPLAY_FORUM TRUE}

{LOOP MESSAGES}
  {VAR MESSAGE MESSAGES}
  {INCLUDE "recent_messages::render_message_row_ajax"}
{/LOOP MESSAGES}
