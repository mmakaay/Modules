{VAR soundclick_player "http://www.soundclick.com/player/V2/mp3player.swf"}

<div class="mod_embed_soundclick">
  <div class="mod_embed_soundclick_player">
    <object type="application/x-shockwave-flash"
          allowScriptAccess="never"
          allowNetworking="internal"
          height="60" width="473"
          data="{soundclick_player}">
      <embed type="application/x-shockwave-flash"
             allowScriptAccess="never"
             allowNetworking="internal"
             height="60" width="473"
             src="{soundclick_player}">
  
        <param name="allowScriptAccess" value="never" />
        <param name="allowNetworking" value="internal" />
        <param name="movie" value="{soundclick_player}" />
        <param name="loop" value="false" />
        <param name="menu" value="false" />
        <param name="quality" value="high" />
        <param name="wmode" value="transparent" />
        <param name="flashvars" value="playType=single&songid={PLAYER->SONG_ID}&q=hi&ext=1&ref=11&autoplay=0" />
        <param name="scale" value="noscale" />
        <param name="salign" value="b" />
        <param name="bgcolor" value="#eeeeee" />
  
      </embed>
    </object><br/>
  </div>
  <div class="mod_embed_soundclick_info">
    <a href="{PLAYER->URL}">{PLAYER->NAME}</a>
  </div>
</div>
