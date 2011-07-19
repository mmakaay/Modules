{VAR DISPLAY_FORUM TRUE}

{VAR PREVDATE "bigbang"}

{LOOP MESSAGES}

  {VAR MESSAGE MESSAGES}
  {VAR MESSAGE->datestamp MESSAGE->time}
  {VAR MESSAGE->lastpost  MESSAGE->time}

  {IF NOT PREVDATE MESSAGE->group_date}
  <div class="prm-separator">
    &#187; {MESSAGE->group_date}
  </div>
  {VAR PREVDATE MESSAGE->group_date}
  {/IF}

  {INCLUDE "recent_messages::render_message_row_ajax"}

{/LOOP MESSAGES}
