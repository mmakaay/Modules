<br/> {! Make sure the player starts on a new line }

<div class="mod_embed_mp3">

  <object type="application/x-shockwave-flash"
          data="{URL->HTTP_PATH}/mods/embed_mp3/audio-player/player.swf"
          id="audioplayer{PLAYER->ID}" height="24" width="290">
    <param name="movie"
           value="{URL->HTTP_PATH}/mods/embed_mp3/audio-player/player.swf"/>
    <param name="FlashVars"
           value="playerID={PLAYER->ID}&amp;soundFile={PLAYER->FLASHURL}"/>
    <param name="quality" value="high"/>
    <param name="menu" value="false"/>
    <param name="bgcolor" value="#f5f5f5"/>
  </object><br/>

  <div class="mod_embed_mp3_info">
    {IF PLAYER->DOWNLOAD}
      <a href="{PLAYER->URL}">{LANG->AttachOpen}</a> |
      <a href="{PLAYER->DOWNLOAD}">{LANG->AttachDownload}</a> -
      {PLAYER->NAME}
    {ELSE}
      <a href="{PLAYER->URL}">{PLAYER->NAME}</a>
    {/IF}
  </div>
</div>
