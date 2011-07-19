<tr>
  <th colspan="2" style="text-align:right">
    {IF URL->PREV_PAGE}
      <a style="color:white" href="{URL->PREV_PAGE}">{LANG->mod_recent_messages->NewerMessages}</a>
    {/IF}
    {IF URL->PREV_PAGE AND URL->NEXT_PAGE}
      &nbsp;&bull;&nbsp;
    {/IF}
    {IF URL->NEXT_PAGE}
      <a style="color:white" href="{URL->NEXT_PAGE}">{LANG->mod_recent_messages->OlderMessages}</a>
    {/IF}
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {LANG->Page} {PAGE}
  </th>
</tr>

</table>


