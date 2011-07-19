{VAR DISPLAY_FORUM TRUE}

{VAR PREVDATE "bigbang"}

{LOOP MESSAGES}

  {VAR MESSAGE MESSAGES}
  {VAR MESSAGE->datestamp MESSAGE->time}
  {VAR MESSAGE->lastpost  MESSAGE->time}

  {IF NOT PREVDATE MESSAGE->group_date}
    {VAR GROUP_HEADING MESSAGE->group_date}
    {INCLUDE "recent_messages::render_group_row"}
    {VAR PREVDATE MESSAGE->group_date}
  {/IF}

  {INCLUDE "recent_messages::render_message_row"}

{/LOOP MESSAGES}
