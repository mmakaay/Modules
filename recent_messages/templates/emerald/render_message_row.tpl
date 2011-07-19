{IF SHOW_FORUM}{VAR DISPLAY_FORUM 0}{/IF}

{VAR subjectclass ""}

{IF MESSAGE->sort PHORUM_SORT_STICKY}
    {IF MESSAGE->new}
        {VAR subjectclass "message-new"}
        {VAR icon "flag_red"}
            {VAR alt LANG->NewMessage}
    {ELSE}
        {VAR icon "bell"}
            {VAR alt LANG->Sticky}
    {/IF}
    {VAR title LANG->Sticky}
{ELSEIF MESSAGE->moved}
    {VAR icon "page_go"}
    {VAR title LANG->MovedSubject}
    {VAR alt LANG->MovedSubject}
{ELSEIF MESSAGE->new}
    {VAR subjectclass "message-new"}
    {VAR icon "flag_red"}
    {VAR title LANG->NewMessage}
        {VAR alt LANG->NewMessage}
{ELSE}
    {VAR icon "comment"}
    {VAR title ""}
    {VAR alt ""}
{/IF}

<tr>
  <td align="center">
    <a href="{IF MESSAGE->new}{MESSAGE->URL->NEWPOST}{ELSE}{MESSAGE->URL->READ}{/IF}" title="{title}"><img src="{URL->TEMPLATE}/images/{icon}.png" class="icon1616" alt="{alt}" /></a>
  </td>

  <td>
    <h4><a {IF subjectclass}class="{subjectclass}"{/IF}
           href="{MESSAGE->URL->READ}">{MESSAGE->subject}</a></h4>
    {IF DISPLAY_FORUM}
      <small>
        {LANG->Forum}:
        <a href="{MESSAGE->URL->FORUM}">{MESSAGE->forum_name}</a>
      </small>
    {/IF}

  </td>

  <td class="author" align="left">
    {IF VIEW_TYPE 2}
      {IF MESSAGE->URL->RECENT_AUTHOR_PROFILE}<a href="{MESSAGE->URL->RECENT_AUTHOR_PROFILE}">{/IF}
        {MESSAGE->recent_author}
      {IF MESSAGE->URL->RECENT_AUTHOR_PROFILE}</a>{/IF}
      <br/>
      {MESSAGE->lastpost}
    {ELSE}
      {IF MESSAGE->URL->PROFILE}<a href="{MESSAGE->URL->PROFILE}">{/IF}
        {MESSAGE->author}
      {IF MESSAGE->URL->PROFILE}</a>{/IF}
      <br />
      {MESSAGE->datestamp}
    {/IF}
  </td>

</tr>
