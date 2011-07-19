<?php

function phorum_mod_pm_keep_copy_by_default()
{
    global $PHORUM;

    if (phorum_page == 'pm' && empty($_POST) &&
        isset($PHORUM['args']['page']) &&
        $PHORUM['args']['page'] == 'send') {
        $PHORUM['DATA']['MESSAGE']['keep'] = 1;
    }
}

?>
