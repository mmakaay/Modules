{! This template is used for formatting moderator lists on the index page }
{! in case automatic displaying is enabled in the module settings. The    }
{! rendered moderator list will be appended to the forum description.     }

{IF MODERATORS}
  <span class="mod_show_moderators_index">
    <br/>
    {IF MODERATOR_COUNT 1}
      {LANG->Moderator}:
    {ELSE}
      {LANG->Moderators}:
    {/IF}
    {VAR IS_FIRST TRUE}
    {LOOP MODERATORS}{IF NOT IS_FIRST}, {/IF}<a href="{MODERATORS->PROFILE}">{MODERATORS->DISPLAY_NAME}</a>{VAR IS_FIRST FALSE}{/LOOP MODERATORS}
  </span>
{/IF}

