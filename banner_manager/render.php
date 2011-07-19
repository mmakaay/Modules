<?php
if(!defined("PHORUM") && !defined("PHORUM_ADMIN")) return;

function phorum_mod_banner_manager_render($data, $for_preview = false)
{
    $cache = $GLOBALS["PHORUM"]["cache"];
    $outfile = $cache . "/banner_manager_" . md5($data["timestamp"] . $data["id"]);

    if (! file_exists($outfile)) {
        if ($fp = fopen($outfile, "w")) {
            fputs($fp, $data["block"]);
            fclose($fp);
        } else {
            print "[Error writing banner file to " . htmlspecialchars($outfile) . "]";
            return;
        }
    }

    ob_start();
    include($outfile);
    $block = ob_get_contents();
    ob_end_clean();

    if ($for_preview) unlink($outfile); 

    return $block;
}
?>
