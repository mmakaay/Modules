<style type="text/css">
.panel_main td {
    vertical-align: top;
    border-bottom: 1px solid #cca;
}
.panel_main {
    border: 2px solid Navy;
    padding: 10px;
}
.panel_menu {
    padding: 0px;
    margin: 0px;
}
.panel {
    display: inline;
    padding: 2px;
    margin: 0px 2px;
    padding: 3px 10px 0px 10px;
    background-color: #DDDDEA;
    color: black;
    font-family: "Trebuchet MS",Verdana, Arial, Helvetica, sans-serif;
    font-size: 14px;
    text-decoration: none;
}

.panel_selected {
    background-color: Navy;
    color: white;
}
</style>

<?php
    if (!defined("PHORUM_ADMIN")) return;

    require_once("./mods/banner_manager/defaults.php");
    require_once("./mods/banner_manager/render.php");

    print "<h1>Banner Manager Module Settings</h1>";

    // ----------------------------------------------------------------------
    // Panel selection menu
    // ----------------------------------------------------------------------

    // The panels that we use in this settings script.
    $panels = array(
        "main"        => "Module settings",
        "banners"     => "Define banner blocks",
        "pages"       => "Link banner blocks to pages"
    );

    // Determine what panel to show.
    if (isset($_POST["panel"])) $panel = $_POST["panel"];
    elseif (isset($_GET["panel"])) $panel = $_GET["panel"];
    if (!isset($panel) || ! isset($panels[$panel])) { $panel = "main"; }
    $panel = basename($panel);

    // Display the panel selection tabs.
    print "<div class=\"panel_menu\">";
    foreach ($panels as $id => $desc) {
        $class = $id == $panel ? 'panel panel_selected' : 'panel';
        $url = phorum_admin_build_url(array(
            'module=modsettings',
            'mod=banner_manager',
            'panel=' . urlencode($id)
        ));
        print "<a class=\"{$class}\" href=\"$url\">";
        print htmlspecialchars($desc);
        print "</a>";
    }
    print "</div>";

    // ----------------------------------------------------------------------
    // Main panel
    // ----------------------------------------------------------------------

    print "<div class=\"panel_main\" style=\"width:95%\">";
    if (isset($panels[$panel])) {
        include("./mods/banner_manager/settings_$panel.php");
    }
    print "</div>";

    // ----------------------------------------------------------------------
    // General panel implementation functions
    // ----------------------------------------------------------------------

    function show_error($msg)
    {
        print "<div class=\"PhorumAdminError\">";
        print "Error: " . htmlspecialchars($msg);
        print "</div>";
    }

    function banner_manager_save_settings()
    {
        if (!phorum_db_update_settings(array(
            "mod_banner_manager" => $GLOBALS["PHORUM"]["mod_banner_manager"]
        ))) {
            die("Fatal error: database error while updating settings");
        }
    }

    function banner_manager_save_success()
    {
        print "<div class=\"PhorumAdminOkMsg\">Settings successfully saved</div>";
    }

?>
