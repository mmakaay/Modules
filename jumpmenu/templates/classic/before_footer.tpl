{! ------------------------------------------------------------------------- }
{! This is some special code that will automatically create the              }
{! forum jumpmenu in a nice spot (inside the forum description div) for      }
{! the classic template. If the javascript code does not detect the div, no  }
{! menu will be shown at all. If you are creating your own template set for  }
{! this module, then you can simply make sure that you have some element     }
{! with id="mod_jumpmenu_root" in your main template at the spot where you   }
{! want the jumpmenu to appear. This code should automatically detect it and }
{! use that one as the jumpmenu root node.                                   }
{! ------------------------------------------------------------------------- }

<script type="text/javascript">
//<![CDATA[

// See if we already have an element with id="mod_jumpmenu" in the page.
var m = document.getElementById('mod_jumpmenu_root');

// Try to detect emerald-like template if no element was found.
// If we do detect this template, then create the mod_jumpmenu element
// on the fly at a useful spot in the page.
if (!m) {
    var divs = document.getElementsByTagName('div');
    var p = null;
    for (var i = 0; i < divs.length; i++) {
        if (divs[i].className == 'PDDiv') {
            p = divs[i];
            break;
        }
    }
    var f = null;
    for (var i = 0; i < p.childNodes.length; i++) {
        if (p.childNodes[i].className &&
            p.childNodes[i].className.indexOf('PhorumFloatingText') != -1) {
            f = p.childNodes[i];
        }
    }

    if (f) {
        m = document.createElement('a');
        m.id        = 'mod_jumpmenu_root';
        m.className = 'mod_jumpmenu_root';
        m.rel       = 'mod_jumpmenu_left';
        m.innerHTML = '{LANG->mod_jumpmenu->JumpTo}';

        if (f.childNodes.length == 0) {
            f.appendChild(m);
        } else {
            f.insertBefore(m, f.childNodes[0]);
        }
    }
}

//]]>
</script>
