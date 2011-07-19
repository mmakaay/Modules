{! ------------------------------------------------------------------------- }
{! This is some special code that will automatically create the              }
{! forum jumpmenu in a nice spot (inside the breadcrumb div) for the emerald }
{! template. If the javascript code does not detect the breadcrumb div, no   }
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
    var b = document.getElementById('breadcrumb');
    if (!b) b = document.getElementById('phorum-breadcrumbs');
    if (b)
    {
        m = document.createElement('a');
        m.id        = 'mod_jumpmenu_root';
        m.className = 'mod_jumpmenu_root';
        m.rel       = 'mod_jumpmenu_left';
        m.innerHTML = '{LANG->mod_jumpmenu->JumpTo}';

        if (b.childNodes.length == 0) {
            b.appendChild(m);
        } else {
            b.insertBefore(m, b.childNodes[0]);
        }
    }
}

//]]>
</script>
