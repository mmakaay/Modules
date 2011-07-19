For running jQuery LightBox, a JavaScript library and CSS definitions
are needed. This module will automatically load the required files.
However, if you are already loading them yourself from your web site's
template, this might result in conflicts. You can use the options
below to suppress the automatic loading.<br/>
<br/>
<?php

print $frm->checkbox("jquery_lightbox_noload", "1", "Do not load the jQuery LightBox JavaScript library + CSS definitions", $PHORUM["mod_embed_images"]["jquery_lightbox_noload"]) . "<br/>";

?>
