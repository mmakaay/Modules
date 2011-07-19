<div class="message"
     style="width:100%;
            padding: 0px;
            margin-top:10px;
            margin-bottom:10px">
 <div class="generic" style="padding: 0px"></div>
 <div class="message-body">

  <strong>{LANG->mod_forumstats->ForumStatistics}</strong>
  {IF mod_forumstats->GlobalStatsLine}
  <p>
  <small>
  <strong>{LANG->mod_forumstats->GlobalStatsHeadline}</strong><br />
  {mod_forumstats->GlobalStatsLine}
  {IF mod_forumstats->recent_user_profile}
  <br />{LANG->mod_forumstats->MostRecentUser} <a href="{mod_forumstats->recent_user_profile}">{mod_forumstats->recent_user_name}</a>.
  {/IF}
  </small>
  </p>
  {/IF}
  {IF mod_forumstats->LocalStatsLine AND mod_forumstats->GlobalStatsLine}
  <hr />
  {/IF}
  {IF mod_forumstats->LocalStatsLine}
  <p>
  <small><strong>{LANG->mod_forumstats->LocalStatsHeadline}</strong><br />
  {mod_forumstats->LocalStatsLine}</small>
  </p>
  {/IF}
 </div>
</div>
