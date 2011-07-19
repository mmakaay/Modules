<table border="0" cellspacing="0" class="list">

<tr>
  <th width="1%">&nbsp;</th>
  <th align="left" width="80%">
    {LANG->Subject}
  </th>
  <th align="left" width="19%" nowrap="nowrap">
    {IF VIEW_TYPE 2}
      {LANG->LastPost}
    {ELSE}
      {LANG->Author}
    {/IF}
  </th>
</tr>

{IF NO_MESSAGES}
  <tr>
    <td class="even nomessages" colspan="3">
      {NO_MESSAGES}
    </td>
  </tr>
{/IF}

