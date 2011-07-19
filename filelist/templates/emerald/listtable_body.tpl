{LOOP FILES}
  <tr>
    <td>
      <a href="{FILES->URL->FILE}">{FILES->NAME}</a>
    </td>
    <td>
      {FILES->SIZE}
    </td>
    <td>
      <a href="{FILES->URL->FILE}">{LANG->AttachOpen}</a> |
      <a href="{FILES->URL->DOWNLOAD}">{LANG->AttachDownload}</a>
    </td>
    <td>
    {IF FILES->URL->PROFILE}
      <a href="{FILES->URL->PROFILE}">{FILES->AUTHOR}</a></td>
    {ELSE}
      {FILES->AUTHOR}
    {/IF}
    <td>
      {FILES->DATESTAMP}
    </td>
    <td>
      <a href="{FILES->URL->READ}">{LANG->mod_filelist->ReadMessage}</a>
    </td>
  </tr>
{/LOOP FILES}
