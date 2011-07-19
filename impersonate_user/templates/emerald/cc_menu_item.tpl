{IF IMPERSONATE_USER_PANEL_ACTIVE}
  {VAR MENU_ITEM_CLASS 'class="current"'}
{ELSE}
  {VAR MENU_ITEM_CLASS ""}
{/IF}

<li>
  <a {MENU_ITEM_CLASS} href="{URL->CC_IMPERSONATE_USER}">
    {LANG->mod_impersonate_user->CCMenuItem}
  </a>
</li>

