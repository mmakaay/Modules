<table width="100%"><tr><td align="left">
<div id="breadcrumb" class="breadcrumb">
  {VAR FIRST TRUE}
  {LOOP BREADCRUMBS}
    {IF NOT FIRST} &gt;{/IF}
    {IF BREADCRUMBS->URL}
      <a {IF BREADCRUMBS->ID AND BREADCRUMBS->TYPE}rel="breadcrumb-{BREADCRUMBS->TYPE}[{BREADCRUMBS->ID}]"{/IF} href="{BREADCRUMBS->URL}">{BREADCRUMBS->TEXT}</a>
    {ELSE}
      {BREADCRUMBS->TEXT}
    {/IF}
    {VAR FIRST FALSE}
  {/LOOP BREADCRUMBS}
</div>
</td><td align="right">
  <div style="text-align:right; margin-bottom:5px">
<form action="{URL->RECENT_MESSAGES}" method="post">
  {POST_VARS}

    <select name="show_amount">
      <option value="10" {IF SHOW_AMOUNT 10}selected="selected"{/IF}>10</option>
      <option value="25" {IF SHOW_AMOUNT 25}selected="selected"{/IF}>25</option>
      <option value="50" {IF SHOW_AMOUNT 50}selected="selected"{/IF}>50</option>
      <option value="100" {IF SHOW_AMOUNT 100}selected="selected"{/IF}>100</option>
    </select>

    <select name="view_type">
      <option value="0" {IF NOT VIEW_TYPE}selected="selected"{/IF}>
        {LANG->mod_recent_messages->Messages}</option>
      {IF ALLOW_UNREAD_MESSAGES}
      <option value="3" {IF VIEW_TYPE 3}selected="selected"{/IF}>
        {LANG->mod_recent_messages->UnreadMessages}</option>
      {/IF}
      <option value="1" {IF VIEW_TYPE 1}selected="selected"{/IF}>
        {LANG->mod_recent_messages->Threads}</option>
      <option value="2" {IF VIEW_TYPE 2}selected="selected"{/IF}>
        {LANG->mod_recent_messages->UpdatedThreads}</option>
    </select>

    <select name="show_forum">
      <option style="padding-left:5px" value="0" {IF SHOW_FORUM 0}selected="selected"{/IF}> {LANG->mod_recent_messages->FromAnyForum}</option>
      {LOOP FORUMS}
        {IF FORUMS->folder_flag}
          <optgroup style="padding-left:5px" label="{FORUMS->indent_spaces}{FORUMS->name}"></optgroup>
        {ELSE}
          <option style="padding-left:5px" value="{FORUMS->forum_id}" {IF FORUMS->forum_id SHOW_FORUM}selected="selected"{/IF}>{FORUMS->indent_spaces}{FORUMS->name}</option>
        {/IF}
      {/LOOP FORUMS}
    </select>

    <select name="group_by">
      <option value="{NO_GROUPING}" {IF GROUP_BY NO_GROUPING}selected="selected"{/IF}>
        {LANG->mod_recent_messages->NoGrouping}</option>
      <option value="{GROUP_BY_DATE}" {IF GROUP_BY GROUP_BY_DATE}selected="selected"{/IF}>
        {LANG->mod_recent_messages->GroupByDate}</option>
      <option value="{GROUP_BY_FORUM}" {IF GROUP_BY GROUP_BY_FORUM}selected="selected"{/IF}>
        {LANG->mod_recent_messages->GroupByForum}</option>
      <option value="{GROUP_BY_FOLDER}" {IF GROUP_BY GROUP_BY_FOLDER}selected="selected"{/IF}>
        {LANG->mod_recent_messages->GroupByFolder}</option>
    </select>

    <input type="submit" value="{LANG->Go}" />

  </div>
</form>
</td></tr></table>
