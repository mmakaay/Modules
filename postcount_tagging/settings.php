<?php

if (!defined("PHORUM_ADMIN")) return;

require_once('./mods/postcount_tagging/api.php');

// Handle module installation:
// Load the module installation code if this was not yet done.
// The installation code will take care of automatically adding
// the custom profile field that is needed for this module.
if (! isset($PHORUM["mod_postcount_tagging_installed"]) ||
    ! $PHORUM["mod_postcount_tagging_installed"]) {
    include("./mods/postcount_tagging/install.php");
}

postcount_tagging_init();

// Easy access to the base URL for admin panels for this module.
$base_url = $PHORUM['admin_http_path'] .
            '?module=modsettings&mod=postcount_tagging';
?>

<h1>Post Count Tagging Settings</h1>

<div style="padding-bottom: 5px">
  <a href="<?php print $base_url ?>">List of rules</a> |
  <a href="<?php print $base_url ?>&edit_rule=1">Add a new rule</a> |
  <a href="<?php print $base_url ?>&list_ignore=1">List of vroots and forums to ignore</a> |
  <a href="<?php print $base_url ?>&recalculate=1">Recalculate post counts</a>
</div>

<?php

if (isset($_GET['copy_rule'])) {
    include("./mods/postcount_tagging/settings/copy_rule.php");
} elseif (isset($_POST['delete_rule']) || isset($_GET['delete_rule'])) {
    include("./mods/postcount_tagging/settings/delete_rule.php");
} elseif (isset($_POST['edit_rule']) || isset($_GET['edit_rule'])) {
    include("./mods/postcount_tagging/settings/edit_rule.php");
} elseif (isset($_POST['list_ignore']) || isset($_GET['list_ignore'])) {
    include("./mods/postcount_tagging/settings/list_ignore.php");
} elseif (isset($_POST['recalculate']) || isset($_GET['recalculate'])) {
    include("./mods/postcount_tagging/settings/recalculate.php");
} else {
    include("./mods/postcount_tagging/settings/list_rules.php");
}

?>
