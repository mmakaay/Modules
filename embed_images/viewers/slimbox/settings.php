For more info,
<a target="_blank" href="http://www.digitalia.be/software/slimbox">visit the Slimbox website</a>
<br/><br/>
For running Slimbox, some JavaScript libraries and CSS definitions
are needed. This module will automatically load the required files.
However, if you are already loading them yourself from your web site's
template, this might result in conflicts. You can use the options
below to suppress the automatic loading.<br/>
<br/>
Note: If you load your own mootools library, then make sure that
at least the following components are included:<br/>
<ul>
  <li><b>Class:</b> all components
  <li><b>Native:</b> all components
  <li><b>Element:</b> Element.Event
  <li><b>Window:</b> Window.DomReady, Window.Size
  <li><b>Effects:</b> Fx.Style, Fx.Styles (optionally Fx.Transitions)
</ul>
<br/>
<?php

print $frm->checkbox("slimbox_mootools_noload", "1", "Do not load the mootools JavaScript library", $PHORUM["mod_embed_images"]["slimbox_mootools_noload"]) . "<br/>";

print $frm->checkbox("slimbox_noload", "1", "Do not load the Slimbox JavaScript library + CSS definitions", $PHORUM["mod_embed_images"]["slimbox_noload"]) . "<br/>";

?>
