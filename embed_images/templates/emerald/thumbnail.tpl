<div id="div_{VIEWER->ID}"
     class="{IF VIEWER->IS_SCALED OR VIEWER->TARGET_URL}mod_embed_images_extended{ELSE}mod_embed_images{/IF}"
     {IF VIEWER->W}style="width:{VIEWER->W}px"{/IF}>

  {IF VIEWER->ERROR}

    <div class="mod_embed_images_error">
      <strong>Image error</strong><br/>
      {VIEWER->ERROR}<br/>
      {IF VIEWER->URL}
        <a href="{VIEWER->URL}">open image URL</a>
      {/IF}
    </div>

  {ELSE}

    {IF VIEWER->TARGET_URL}
      {VAR TARGET_URL VIEWER->TARGET_URL}
    {ELSE}
      {VAR TARGET_URL VIEWER->URL}
    {/IF}

    <div id="imagediv_{VIEWER->ID}" class="mod_embed_images_image"
         {IF VIEWER->W}style="width:{VIEWER->W}px; height:{VIEWER->H}px"{/IF}>

    {IF NOT VIEWER->IS_CACHED}
      {! If the client does not have scripting abilities, then we'll }
      {! load the image right away. This way, we will not be able to }
      {! supply loading feedback and error reporting, but it will    }
      {! at least work for those clients. Clients that do have       }
      {! scripting support, will load the thumbnail through an Ajax  }
      {! JavaScript based method.                                    }
      <noscript>
      <div>
    {/IF}

    {IF TARGET_URL}<a href="{TARGET_URL}">{/IF}
        <img src="{VIEWER->THUMBNAIL_URL}"
             {IF VIEWER->W}width="{VIEWER->W}"{/IF}
             {IF VIEWER->H}height="{VIEWER->H}"{/IF}
             id="image_{VIEWER->ID}"
             alt="{VIEWER->DESCRIPTION}"
             title="{VIEWER->DESCRIPTION}"/>
    {IF TARGET_URL}</a>{/IF}

    {IF NOT VIEWER->IS_CACHED}
      </div>
      </noscript>
    {/IF}

    </div>

    <div class="mod_embed_images_info {IF VIEWER->TARGET_URL}mod_embed_images_info_link{/IF}" id="info_{VIEWER->ID}"
      {IF VIEWER->IS_SCALED OR VIEWER->TARGET_URL}style="display:block"{/IF}>
      <a id="link_{VIEWER->ID}" href="{TARGET_URL}">{VIEWER->DESCRIPTION}</a>
    </div>

  {/IF}

 </div>


{! For clients with scripting support, we provide image loading feedback.  }
{! This will send an ajax request to the server to check if a scaled       }
{! image can be provided. If not, an error will be shown. If yes, an       }
{! image is created for showing the thumbnail. If a thumbnail is already   }
{! available in the cache, the image will be put in the message body right }
{! away. This JavaScript function will then setup a viewer for the full    }
{! size images if needed.                                                  }
<script type="text/javascript">
mod_embed_images_loadimage(
  '{VIEWER->ID}',
  '{VIEWER->THUMBNAIL_URL}',
  '{VIEWER->URL}',
  '{VIEWER->AJAX_URL}',
  '{VIEWER->TARGET_URL}',
   {VIEWER->MESSAGE_ID},
   {VIEWER->MAX_W}, {VIEWER->MAX_H},
  '<?php print addslashes($PHORUM['DATA']['LANG']['mod_embed_images']['LoadingImage']) ?>',
   false
);
</script>
