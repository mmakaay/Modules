function bbcode_spoiler_editor_tool()
{
    var description = prompt(
        editor_tools_translate("enter spoiler description"), ''
    );
    if (description == null) return; // Cancel clicked.
    description = editor_tools_strip_whitespace(description);

    var starttag = description == ''
                 ? "[spoiler]\n"
                 : "[spoiler=" + description + "]\n";

    editor_tools_add_tags(starttag, "\n[/spoiler]\n", null, editor_tools_translate("enter spoiler content"));
    editor_tools_focus_textarea();
}

function bbcode_spoiler_show(spoiler_id)
{
    var s = document.getElementById('bbcode_spoiler_'+spoiler_id);
    if (!s) {
        alert("Internal error: can't find spoiler div");
        return true;
    }

    // An extra id which can be used in the spoiler template and which
    // will be made visible or invisible, along with the spoiler content.
    // It's intended use is to add some close link at the bottom of
    // the spoiler content.
    var c = document.getElementById('bbcode_spoiler_close_'+spoiler_id);

    // Switch visibility of the spoiler content if the spoiler's content
    // was already loaded. If the display style is "block" then the
    // content could have been included by loading the page through a
    // "view_spoiler" URL. For that case, we set "is_loaded" explicitly
    // here.
    if (s.is_loaded || s.style.display == 'block')
    {
        var a = document.getElementById('bbcode_spoiler_link_'+spoiler_id);

        if (s.style.display == 'block') {
            s.style.display = 'none';
            if (c) c.style.display = 'none';
            s.is_loaded = 1;
            if (a) a.className = 'bbcode_spoiler_link';
        } else {
            s.style.display = 'block';
            if (c) c.style.display = 'block';
            if (a) a.className = 'bbcode_spoiler_link_view';
        }
        return false;
    }

    // Create the XMLHttpRequest object that we can use to send an
    // Ajax request to the server.
    var xhr;
    if (window.XMLHttpRequest) {
        xhr = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        var versions = [
            "MSXML2.XMLHttp.5.0", "MSXML2.XMLHttp.4.0",
            "MSXML2.XMLHttp.3.0", "MSXML2.XMLHttp",
            "Microsoft.XMLHttp"
        ];
        for (var i=0; i < versions.length; i++)
          try { xhr = new ActiveXObject(versions[i]); } catch (e) { }
    }

    if (!xhr) return true;

    // Setup a loading... indicator.
    var a = document.getElementById('bbcode_spoiler_link_'+spoiler_id);
    if (a) a.className = 'bbcode_spoiler_link_loading';

    // Send the request to the server.
    xhr.open("post", bbcode_spoiler_ajax_url, true);
    xhr.setRequestHeader("Content-Type", "text/plain");
    xhr.onreadystatechange = function()
    {
        if (xhr.readyState != 4) return;

        if (xhr.status == 200) {
            addToElement(s, xhr.responseText);
            if (a) a.className = 'bbcode_spoiler_link_view';
            if (c) c.style.display = 'block';
            s.style.display = 'block';
            s.is_loaded = 1;
        }
    }
    xhr.send("retrieve "+spoiler_id);

    return false;
}

function addToElement(elt, response)
{
    var cursor = 0;
    var start  = 1;
    var end    = 1;

    // Add the response data to the page element.
    elt.innerHTML = response;

    // Parse out javascript blocks to eval them. Adding them to the
    // page using innerHTML does not invoke parsing by the browser.
    while (cursor < response.length && start > 0 && end > 0) {
        start = response.indexOf('<script', cursor);
        end   = response.indexOf('<\/script', cursor);
        if (end > start && end > -1) {
            if (start > -1) {
                var res = response.substring(start, end);
                start = res.indexOf('>') + 1;
                res = res.substring(start);
                if (res.length != 0) {
                    eval(res);
                }
            }
            cursor = end + 1;
        }
    }
}
