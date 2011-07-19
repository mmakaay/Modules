For some info on this viewer, check out
<a target="_new"
   href="http://www.dynamicdrive.com/dynamicindex4/thumbnail.htm">Dynamic
   Drive's web site</a>.<br/>
<br/>
The viewer can make use of a fading animation when opening the image viewer
window. Note however that in slow browsers and for some color combinations,
 this might not look too good.<br/>
<br/>
<?php
print $frm->checkbox("dynadrive_animate", "1", "Enable the animation", $PHORUM["mod_embed_images"]["dynadrive_animate"]);
?>

<br/><br/>
For running Dynamic Drive's thumbnail viewer, a JavaScript library and
CSS definitions are needed. This module will automatically load the required
files. However, if you are already loading them yourself from your web site's
template, this might result in conflicts. You can use the option
below to suppress the automatic loading.<br/>
<br/>
<?php
print $frm->checkbox("dynadrive_noload", "1", "Do not load the the JavaScript and CSS code", $PHORUM["mod_embed_images"]["dynadrive_noload"]);
?>

