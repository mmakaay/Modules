function embed_mp3_editor_tool()
{
    var url = 'http://';

    for (;;)
    {
        // Read input.
        url = prompt(editor_tools_translate("enter mp3 url"), url);
        if (url == null) return; // Cancel clicked.
        url = editor_tools_strip_whitespace(url);

        // Check the URL scheme (http, https, ftp and mailto are allowed).
        var copy = url.toLowerCase();
        if (copy == 'http://' || (
            copy.substring(0,7) != 'http://' &&
            copy.substring(0,8) != 'https://' &&
            copy.substring(0,6) != 'ftp://')) {
            alert(editor_tools_translate("invalid mp3 url"));
            continue;
        }

        break;
    }

    editor_tools_add_tags('[mp3]' + url + '[/mp3]', '');
    editor_tools_focus_textarea();
}
