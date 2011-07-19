{IF NOT AJAX_CONTENT_ONLY}<div class="prm-container">{/IF}

  {IF GROUP_BY NO_GROUPING}
    {INCLUDE "recent_messages::view_messages"}
  {ELSEIF GROUP_BY GROUP_BY_DATE}
    {INCLUDE "recent_messages::view_by_date"}
  {ELSEIF GROUP_BY GROUP_BY_FORUM}
    {INCLUDE "recent_messages::view_by_forum"}
  {ELSEIF GROUP_BY GROUP_BY_FOLDER}
    {INCLUDE "recent_messages::view_by_folder"}
  {/IF}

{IF NOT AJAX_CONTENT_ONLY}</div>{/IF}
