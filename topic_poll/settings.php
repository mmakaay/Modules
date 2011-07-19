<?php
if (!defined("PHORUM_ADMIN")) return;

    // If a forum was selected, then show the forum settings screen.
    // Else, show the forum selection screen.
    $forum_id = NULL;
    if (isset($_POST["forum_id"])) {
        $forum_id = (int)$_POST["forum_id"];
    } else {
        foreach ($_POST as $key => $val) {
            if (preg_match('/^edit:(\d+)$/', $key, $m)) {
                $forum_id = $m[1];
            }
        }
    }

    if ($forum_id) {
        include("./mods/topic_poll/settings.editforum.php");
    } else {
        include("./mods/topic_poll/settings.selectforum.php");
    }
?>
