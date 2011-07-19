#mod_quick_reply {
    display: none;
    position: absolute;
    font-size: {font_small};
    margin-top: 3px;
    padding-bottom: 10px;
}

#mod_quick_reply form {
    display: inline;
    padding: 0;
    margin: 0;
}

#mod_quick_reply .quick_border {
    border: 2px solid {border_color};
    background-color: {alt_background_color};
}

#mod_quick_reply table {
    border-collapse: collapse;
    padding: 0;
    margin: 0;
}

#mod_quick_reply table td {
    vertical-align: top;
    padding: 15px;
}

#mod_quick_reply table th {
    background-repeat: repeat-x;
    color: {border_font_color};
    background-image: url('{header_background_image}');
    background-color: {border_color};
    padding: 2px 5px;
    text-align: left;
}

#phorum #mod_quick_reply_active_link {
    text-decoration: none;
}

#mod_quick_reply table tr.quick_subject td {
    padding-bottom: 0;
}

#mod_quick_reply table tr.quick_body td {
    padding-top: 5px;
}

#mod_quick_reply table tr.quick_body textarea,
#mod_quick_reply table tr.quick_subject input {
    width: 100%;
}

#mod_quick_reply tr.quick_buttons td {
    padding-top: 0px;
}

#mod_quick_reply tr.quick_buttons input {
}
