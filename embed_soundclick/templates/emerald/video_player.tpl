{VAR soundclick_player "http://www.soundclick.com/player/v2/videoPlayer.swf"}

<div class="mod_embed_soundclick_video">
  <div class="mod_embed_soundclick_video_player">
    <embed src="{soundclick_player}"
           FlashVars="vidID={PLAYER->VIDEO_ID}"
           quality="high"
           bgcolor="#cccccc"
           width="424"
           height="346"
           name="VideoPlayer"
           allowFullScreen="true"
           type="application/x-shockwave-flash"
           pluginspage="http://www.macromedia.com/go/getflashplayer">
    </embed>
  </div>
  <div class="mod_embed_soundclick_video_info">
    <a href="{PLAYER->URL}">{PLAYER->NAME}</a>
  </div>
</div>
