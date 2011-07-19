<div class="PhorumNavBlock" style="text-align: left;">
  <span class="PhorumNavHeading PhorumHeadingLeft">{LANG->Goto}:</span>&nbsp;{IF URL->INDEX}<a class="PhorumNavLink" href="{URL->INDEX}">{LANG->ForumList}</a>&bull;{/IF}<a class="PhorumNavLink" href="{URL->SEARCH}">{LANG->Search}</a>&bull;{INCLUDE "loginout_menu"}
</div>

<table border="0" cellspacing="0" class="PhorumStdTable">
<tr>
  <th align="left" width="80%" class="PhorumTableHeader">
    {LANG->Subject}
  </th>
  <th align="left" width="19%" nowrap="nowrap" class="PhorumTableHeader">
    {IF VIEW_TYPE 2}
      {LANG->LastPost}
    {ELSE}
      {LANG->Author}
    {/IF}
  </th>
</tr>

{IF NO_MESSAGES}
  <tr>
    <td class="even" colspan="3" style="text-align:center; padding:30px" class="PhorumTableHeader">
      {NO_MESSAGES}
    </td>
  </tr>
{/IF}

