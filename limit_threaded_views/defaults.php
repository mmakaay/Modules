<?php

if (!isset($PHORUM['mod_limit_threaded_views']['list_threaded'])) {
    $PHORUM['mod_limit_threaded_views']['list_threaded'] = 500;
}
if (!isset($PHORUM['mod_limit_threaded_views']['read_threaded'])) {
    $PHORUM['mod_limit_threaded_views']['read_threaded'] = 500;
}
if (!isset($PHORUM['mod_limit_threaded_views']['read_hybrid'])) {
    $PHORUM['mod_limit_threaded_views']['read_hybrid'] = 250;
}
if (!isset($PHORUM['mod_limit_threaded_views']['show_notice'])) {
    $PHORUM['mod_limit_threaded_views']['show_notice'] = 1;
}

?>
