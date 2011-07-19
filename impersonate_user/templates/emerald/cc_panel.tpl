<form action="{URL->ACTION}" method="POST">
{POST_VARS}

<div class="generic">
  <h4>{LANG->mod_impersonate_user->CCSearchHeader}</h4>

  <table cellspacing="0" cellpadding="5">
  <tr>
    <td>
      {LANG->Username}:
    </td>
    <td>
      <input type="text" name="search_username" value="{SEARCH_USERNAME}"/>
    </td>
    <td>
      {LANG->mod_impersonate_user->UserID}:
    </td>
    <td>
      <input type="text" name="search_user_id" value="{SEARCH_USER_ID}"/>
    </td>
  </tr>
  <tr>
    <td>
      {LANG->mod_impersonate_user->DisplayName}:
    </td>
    <td>
      <input type="text" name="search_display_name" value="{SEARCH_DISPLAY_NAME}"/>
    </td>
    <td>
      {LANG->Email}:
    </td>
    <td>
      <input type="text" name="search_email" value="{SEARCH_EMAIL}"/>
    </td>
  </tr>
  <tr>
  </table>

  <input type="submit" name="do_search" value="{LANG->Search}"/>

</div>

</form>

<br/>

{IF NO_USERS_FOUND}
  <strong>{LANG->NoResults}</strong>
{/IF}
{IF TOO_MANY_USERS_FOUND}
  <strong>{LANG->mod_impersonate_user->TooManyResults}</strong>
  <br/><br/>
{/IF}

{IF NOT USERS_COUNT 0}

  <table style="width:100%" cellpadding="5" cellspacing="0" class="list">
  <tr>
    <th style="text-align:left; white-space:nowrap">{LANG->mod_impersonate_user->UserID}</th>
    <th style="text-align:left; white-space:nowrap">{LANG->mod_impersonate_user->UserDetails}</th>
    <th>&nbsp;</th>
  </tr>
  {LOOP USERS}
    <tr>
      <td>{USERS->user_id}</td>
      <td width="100%">
        {LANG->Username}: {USERS->username}<br/>
        {LANG->Email}: {USERS->email}<br/>
        {LANG->mod_impersonate_user->DisplayName}: {USERS->display_name}
      </td>
      <td style="white-space:nowrap">
        <img src="{MOD_IMPERSONATE_USER->URL->TEMPLATES}/key_go.png"/>
        <a href="{USERS->URL->IMPERSONATE_USER}">
          {LANG->mod_impersonate_user->ImpersonateUser}
        </a><br/>
        <img src="{MOD_IMPERSONATE_USER->URL->TEMPLATES}/user.png"/>
        <a href="{USERS->URL->PROFILE}">
          {LANG->UserProfile}
        </a><br/>
        <img src="{MOD_IMPERSONATE_USER->URL->TEMPLATES}/pm.png"/>
        <a href="{USERS->URL->PM}">
          {LANG->SendPM}
        </a>
      </td>
    </tr>
  {/LOOP USERS}
  </table>
{/IF}


