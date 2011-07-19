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
  <td class="PhorumTableRow{altclass}">
        {marker}
{IF MESSAGE->sort PHORUM_SORT_STICKY}
<span class="PhorumListSubjPrefix">{LANG->Sticky}:</span>
{/IF}


        {IF MESSAGES->moved}<span class="PhorumListSubjPrefix">{LANG->MovedSubject}:</span>{/IF}
<a href="{MESSAGE->URL->READ}">{MESSAGE->subject}</a>
{IF MESSAGE->new}&nbsp;<span class="PhorumNewFlag">{LANG->newflag}</span>{/IF}

    {IF DISPLAY_FORUM}
      <br />{LANG->Forum}:
      <a href="{MESSAGE->URL->FORUM}">{MESSAGE->forum_name}</a>
    {/IF}

  </td>

      <td class="PhorumTableRow{altclass}" nowrap="nowrap">
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
