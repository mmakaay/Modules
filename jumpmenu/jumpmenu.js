var PhorumJumpMenu =
{
    active      : {},
    visible     : {},
    menuspacing : -1,
    timer       : null,
    vroot       : 0, // will be set separately from the jumpmenu.php code

    init: function ()
    {
        // Make sure that events will not fire at unload time, because that
        // generates JavaScript warnings in Firefox about PhorumJumpMenu
        // that does not exist anymore at that time.
        var oldunload = window.onunload;
        window.onunload = function ()
        {
            if (oldunload) {
                oldunload();
            }
            var root = document.getElementById('mod_jumpmenu_root');
            if (root) {
                root.onmouseover = null;
                root.onmouseout  = null;
            }
            var ahrefs = document.getElementsByTagName('a');
            for (var i = 0; i < ahrefs.length; i += 1)
            {
                if (!ahrefs[i].rel) {
                    continue;
                }
                var obj = ahrefs[i];

                if (obj.rel.indexOf('mod_jumpmenu_') === 0) {
                    obj.onmouseover = null;
                    obj.onmouseout  = null;
                }
            }
        }

        // Events for the root jumpmenu item.
        var r = document.getElementById('mod_jumpmenu_root');
        if (!r) {
            return;
        }
        r.onmouseover = function () {
            PhorumJumpMenu.stopCloseTimer();
            PhorumJumpMenu.openSubMenu(null);
        };
        r.onmouseout  = function () {
            PhorumJumpMenu.startCloseTimer();
        };

        // Events for the folders and forums.
        var ahrefs = document.getElementsByTagName('a');
        for (var i = 0; i < ahrefs.length; i += 1)
        {
            if (!ahrefs[i].rel) {
                continue;
            }
            var obj = ahrefs[i];

            if (obj.rel === 'mod_jumpmenu_forum')
            {
                obj.onmouseover = function () {
                    PhorumJumpMenu.stopCloseTimer();
                    PhorumJumpMenu.openSubMenu(this);
                };
                obj.onmouseout  = function () {
                    PhorumJumpMenu.startCloseTimer();
                };
            }
            else if (obj.rel.substring(0, 19) === 'mod_jumpmenu_folder')
            {
                var info = obj.rel.substring(20, obj.rel.length - 1).split(',');
                obj.menu_id   = info[0];
                obj.parent_id = info[1];

                obj.onmouseover = function () {
                    PhorumJumpMenu.stopCloseTimer();
                    PhorumJumpMenu.openSubMenu(this);
                };
                obj.onmouseout  = function () {
                    PhorumJumpMenu.startCloseTimer();
                };
            }
        }
    },

    hideAll: function ()
    {
        for (var depth in PhorumJumpMenu.visible) {
            for (var idx in PhorumJumpMenu.visible[depth]) {
                var o = PhorumJumpMenu.visible[depth][idx];
                $PJ(o).hide();
            }
        }

        PhorumJumpMenu.active  = {};
        PhorumJumpMenu.visible = {};
    },

    startCloseTimer: function ()
    {
        if (PhorumJumpMenu.timer !== null) {
            clearTimeout(PhorumJumpMenu.timer);
        }
        PhorumJumpMenu.timer = setTimeout(function () {
            PhorumJumpMenu.timer = null;
            PhorumJumpMenu.hideAll();
        }, 500);
    },

    stopCloseTimer: function ()
    {
        if (PhorumJumpMenu.timer !== null) {
            clearTimeout(PhorumJumpMenu.timer);
            PhorumJumpMenu.timer = null;
        }
    },

    getScreenInfo: function ()
    {
        var width    = $PJ(window).width();
        var height   = $PJ(window).height();
        var scroll_y = $PJ(window).scrollTop();
        var scroll_x = $PJ(window).scrollLeft();

        return {
            'width'     : width,
            'height'    : height,
            'scroll_x'  : scroll_x,
            'scroll_y'  : scroll_y,
            'visible_x' : width + scroll_x,
            'visible_y' : height + scroll_y
        };
    },

    openSubMenu: function (menu_item)
    {
        // If the menu item is not a folder, then find its parent menu.
        // This way, deeper submenus that are possibly opened
        // will be collapsed.
        var menu = menu_item;
        if (menu_item && menu_item.className.indexOf('mod_jumpmenu_folder') == -1) {
            var n = menu.parentNode.id;
            if (n.indexOf('mod_jumpmenu_menu_content_') == 0) {
                var menu_id = n.substr(26);
                if (menu_id == PhorumJumpMenu.vroot) {
                    menu = null;
                } else {
                    var pid = 'mod_jumpmenu_item_' + menu_id;
                    var p = document.getElementById(pid);
                    if (p) menu = p;
                }
            }
        }

        // Find the parent menu, parent item and child menu.
        var menu_id   = menu == null ? PhorumJumpMenu.vroot : menu.menu_id;
        var parent_id = menu == null ? null : menu.parent_id;
        var p_menu = menu_id == PhorumJumpMenu.vroot 
              ? null
              : document.getElementById('mod_jumpmenu_menu_' + parent_id);
        var p_item = menu_id == PhorumJumpMenu.vroot
              ? document.getElementById('mod_jumpmenu_root')
              : document.getElementById('mod_jumpmenu_item_' + menu_id);
        var c_menu = document.getElementById('mod_jumpmenu_menu_' + menu_id);


        // Handle highlighting of the active menu item.
        // Clear all highlights in the child menu. 
        if (c_menu) {
            var ahrefs = c_menu.getElementsByTagName('a');
            for (var i=0; i<ahrefs.length; i += 1) {
                var idx = ahrefs[i].className.indexOf(' mod_jumpmenu_highlighted'); 
                if (idx != -1) {
                    ahrefs[i].className = ahrefs[i].className.substring(0,idx);
                }
            }
        }
        // If the menu item is a folder, then we also cleanup the
        // parent menu (which is the menu that the user is currently
        // hovering over).
        if (menu_item && menu_item.className.indexOf('mod_jumpmenu_folder') != -1) {
            var ahrefs = p_menu.getElementsByTagName('a');
            for (var i=0; i<ahrefs.length; i += 1) {
                var idx = ahrefs[i].className.indexOf(' mod_jumpmenu_highlighted'); 
                if (idx != -1) {
                    ahrefs[i].className = ahrefs[i].className.substring(0,idx);
                }
            }
        }
        // Highlight the active menu item.
        if (menu_item && menu_item.className.indexOf(' mod_jumpmenu_highlighted') == -1) {
            menu_item.className += ' mod_jumpmenu_highlighted';
        }

        // If we did not find the required objects, then silently ignore
        // the problem and stop processing.
        if (!p_item || !c_menu) return;
        if (menu_id != PhorumJumpMenu.vroot && !p_menu) return;

        // Do some dimension handling and storage the first time that the
        // menu is opened.
        if (!c_menu.jumpmenu_init_done)
        {
            $c_menu = $PJ(c_menu);

            // Without making the menu visible, we don't know the width/height
            // of the menu div, because those are only available after rendering
            // the menu HTML code. So for positioning the menu, we have a
            // chicken/egg problem to fix. I fix it by moving the egg out of
            // the visible screen area before making it visible.
            $c_menu.css({
                top    : '-500px',
                left   : '-500px',
                zIndex : 1000 // This should put the menus on top at most pages.
            }).show();

            // force the widths for the <a href="..."> tags. this is needed to
            // make the links hoverable and clickable over the full menu width
            // in msie6 :(
            // the "var w" is used to prevent variable tag widths due to
            // accidental resizing of the menu while setting the tag widths
            // (i've seen that happening in opera).
            var w = null;
            $c_menu.find('a').each(function () {
                $a = $PJ(this);
                if (!w) w = $a.outerWidth() + 'px';
                $a.css('width', w);
            });

            // store the displaying dimensions in the div object.
            c_menu.jumpmenu_width  = $c_menu.outerWidth();
            c_menu.jumpmenu_height = $c_menu.outerHeight();

            c_menu.jumpmenu_init_done = 1;
            $c_menu.hide();
        }

        // keep track of the menu depth and menu drawing direction.
        // first level menu.
        if (menu_id == PhorumJumpMenu.vroot)
        {
            c_menu.depth = 1;

            // at the first level, we look at the main menu item. if that one
            // uses rel="mod_jumpmenu_left", then the direction of the whole
            // menu structure will be left. by default, this direction is right.
            c_menu.direction = 'right';
            if (p_item.rel && p_item.rel == 'mod_jumpmenu_left') {
                c_menu.direction = 'left';
            }
        }
        // second level and deeper.
        else
        {
            c_menu.depth = p_menu.depth + 1;

            // inherit the parent's menu direction.
            c_menu.direction = p_menu.direction;
        }

        // determine the screen dimensions.
        var scr = PhorumJumpMenu.getScreenInfo();

        // determine the top-left of the parent menu item.
        var p_item_pos = $PJ(p_item).offset();
        var top  = p_item_pos.top;
        var left = p_item_pos.left;

        // determine the location of the child menu.
        // the first level menu is placed directly beneath the main menu object.
        if (menu_id == PhorumJumpMenu.vroot)
        {
            top = top + $PJ(p_item).outerHeight();

            if (c_menu.direction == 'left')
            {
                left = left - (c_menu.jumpmenu_width - $PJ(p_item).outerWidth());
                if (left < scr.scroll_x) left = scr.scroll_x + 2;
            }
            else
            {
                left = left;
                if ((left + c_menu.jumpmenu_width) > scr.width) {
                    left = scr.width - c_menu.jumpmenu_width - 2;
                }
            }
        }
        // second level and deeper levels are handled as standard menu popups
        // (placed to the right or left of the selected item).
        else
        {
            // The left pos is derived from the outer menu container.
            var $menu = $PJ(p_item).closest('.mod_jumpmenu_menu');
            var pos = $menu.offset();
            left = pos.left;

            var dir = c_menu.direction;

            var left_pos =
                left // the left side of the parent menu item
                - PhorumJumpMenu.menuspacing // handle menu spacing
                - c_menu.jumpmenu_width; // add child menu width;

            var right_pos =
                left // the left side of the parent menu item
                + PhorumJumpMenu.menuspacing  // handle menu spacing
                + $menu.outerWidth(); // add parent menu width
;
            if (dir == 'left') {
                if (left_pos < scr.scroll_x) {
                    dir = 'right';
                } else {
                    left = left_pos;
                }
            }

            if (dir == 'right') {
                if ((right_pos + c_menu.jumpmenu_width) > scr.visible_x) {
                    left = left_pos;
                } else {
                    left = right_pos;
                }
            }
        }

        // take extra vertical space into account for adjusting for
        // padding of the menu div.
        if (menu_id != PhorumJumpMenu.vroot) {
            top -= p_item.parentNode.offsetTop;
        }

        // move the menu up if needed.
        if ((top + c_menu.jumpmenu_height) > scr.visible_y) {
            top = scr.visible_y - c_menu.jumpmenu_height - 2;
            if (top < scr.scroll_y) top = scr.scroll_y + 2;
        }

        // move the menu into position and make it visible.
        // The bgiframe is used for working around an MSIE problem.
        $PJ(c_menu)
            .css({ top  : top  + 'px', left : left + 'px' })
            .bgiframe()
            .show()

        // keep track of the active menu for the current menu level.
        PhorumJumpMenu.active[c_menu.depth] = c_menu;

        // cleanup the menus that should not be visible anymore.
        for (var depth in PhorumJumpMenu.visible) {
            for (var idx in PhorumJumpMenu.visible[depth]) {
                var o = PhorumJumpMenu.visible[depth][idx];
                if (depth > c_menu.depth ||
                    o.id != PhorumJumpMenu.active[depth].id) {
                    $PJ(o).hide();
                }
            }
        }

        // keep track of visible menus.
        PhorumJumpMenu.visible[c_menu.depth] = {};
        PhorumJumpMenu.visible[c_menu.depth][c_menu.id] = c_menu;
    }

};
