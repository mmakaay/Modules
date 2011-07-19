{! This template is used for formatting moderator lists on the list and read  }
{! pages, in case automatic displaying is enabled in the module settings.     }
{! The rendered moderator list will be displayed in the page footer.          }

{IF MODERATORS}
  <div style="margin-top:15px" class="generic mod_show_moderators_footer">

    <strong>
      {IF MODERATOR_COUNT 1}
        {LANG->Moderator}:
      {ELSE}
        {LANG->Moderators}:
      {/IF}
    </strong>

      {VAR IS_FIRST TRUE}
      {LOOP MODERATORS}{IF NOT IS_FIRST}, {/IF}<a href="{MODERATORS->PROFILE}">{MODERATORS->DISPLAY_NAME}</a>{VAR IS_FIRST FALSE}{/LOOP MODERATORS}
  </div>
{/IF}

