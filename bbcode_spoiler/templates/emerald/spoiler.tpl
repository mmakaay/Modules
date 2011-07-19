<a name="bbcode_spoiler_anchor_%spoiler_id%"></a>
<div class="bbcode_spoiler">
  <a class="%spoiler_link_class%"
     href="%spoiler_view_url%"
     onclick="return bbcode_spoiler_show('%spoiler_id%')"
     id="bbcode_spoiler_link_%spoiler_id%">
    %spoiler_title%
  </a>
  <div id="bbcode_spoiler_%spoiler_id%"
       style="display:%spoiler_display%"
       class="bbcode_spoiler_inner">
      %spoiler_content%
  </div>
  <br style="clear:both"/>
  <div id="bbcode_spoiler_close_%spoiler_id%"
       style="display:%spoiler_display%; margin-top:10px"
       class="bbcode_spoiler_close">
      <a class="bbcode_spoiler_link_close" href="%spoiler_view_url%"
         onclick="bbcode_spoiler_show('%spoiler_id%'); return false">
          {LANG->mod_bbcode_spoiler->HideSpoiler}
      </a>
  </div>
</div>
