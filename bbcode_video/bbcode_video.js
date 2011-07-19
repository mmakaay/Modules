function bbcode_video_editor_tool()
{
    editor_tools_add_tags(
        '[video]', '[/video]',
        null,
        editor_tools_translate("enter video url")
    );

    editor_tools_focus_textarea();
}
