<?php

if (!defined('PHORUM_ADMIN')) return;

// Handle a posted form.
if (!empty($_POST['edit_rule']) && $_POST['edit_rule'] == 2)
{
    $rule['id']             = (int) $_POST['id'];
    $rule['name']           = trim($_POST['name']);
    $rule['vroot']          = (int) $_POST['vroot'];
    $rule['enable_profile'] = (int) $_POST['vroot'];

    $rule = array(
        "id"             => (int) $_POST['id'],
        "name"           => trim($_POST['name']),
        "vroot"          => (int) $_POST['vroot'],
        "forum"          => (int) $_POST['forum'],
        "enable_profile" => isset($_POST['enable_profile']) ? 1 : 0,
        "enable_read"    => isset($_POST['enable_read']) ? 1 : 0,
        "enable_user"    => isset($_POST['enable_user']) ? 1 : 0,
        "scope"          => $_POST['scope'],
        ">="             => trim($_POST['>=']) == '' ? '' : (int) $_POST['>='],
        ">"              => trim($_POST['>'])  == '' ? '' : (int) $_POST['>'],
        "<="             => trim($_POST['<=']) == '' ? '' : (int) $_POST['<='],
        "<"              => trim($_POST['<'])  == '' ? '' : (int) $_POST['<'],
        "="              => trim($_POST['='])  == '' ? '' : (int) $_POST['='],
        "tpl_var"        => trim($_POST['tpl_var']),
        "tpl_html"       => trim($_POST['tpl_html'])
    );

    $errors = array();

    if ($rule['name'] == '') {
        $errors[] = 'The name / description for the rule is not set.';
    }

    if ($rule['<='].$rule['<'].$rule['>'].$rule['>='].$rule['='] == '') {
        $errors[] = 'No criterium is set. Configure at least one.';
    }

    if ($rule["tpl_var"] == '') {
        $errors[] = 'The template variable name is not set.';
    } elseif (!preg_match('/^[\w_]+$/', $rule["tpl_var"])) {
        $errors[] = 'The template variable name can only contain letters, ' .
                    'numbers and underscore "_" characters.';
    }

    if ($rule['tpl_html'] == '') {
        $errors[] = 'The HTML code to put in the template variable is not set.';
    }

    // Errors in the input?
    if (!empty($errors)) {
        phorum_admin_error(
            'One or more problems were found. Please correct them ' .
            'and try again:<ul><li>' .
            implode('</li><li>', $errors) . '</li></ul>'
        );
    }
    // Everything okay? Then save the rule.
    else
    {
        postcount_tagging_store_rule($rule);

        phorum_admin_okmsg("The rule \"" . htmlspecialchars($rule['name']) . "\" was successfully stored");

        include("./mods/postcount_tagging/settings/list_rules.php");
        return;
    }
}

// Handle initial form.
else
{
    // Edit an existing rule.
    if (isset($_GET['id'])) {
        if (empty($PHORUM['mod_postcount_tagging']['rules'][$_GET['id']])) {
            phorum_admin_error("Cannot edit rule: rule id " .
                               htmlspecialchars($_GET['id']) .
                               " not found");
            include("./mods/postcount_tagging/settings/list_rules.php");
            return;
        }
        $rule = $PHORUM['mod_postcount_tagging']['rules'][$_GET['id']];
    }

    // The default setup for an empty new rule.
    else {
        $rule = array(
            "id"             => 0,
            "name"           => 'Undefined',
            "vroot"          => -1,
            "forum"          => -1,
            "enable_profile" => 0,
            "enable_read"    => 0,
            "enable_user"    => 0,
            "scope"          => "GLOBAL",
            ">="             => '',
            ">"              => '',
            "<="             => '',
            "<"              => '',
            "="              => '',
            "tpl_var"        => '',
            "tpl_html"       => ''
        );
    }
}
$title = $rule['id']
       ? "Edit postcount tagging rule"
       : "Add a new postcount tagging rule";

print '<div class="PhorumAdminTitle">'.$title.'</div>';

// Build the form.
include_once "./include/admin/PhorumInputForm.php";
$frm = new PhorumInputForm ("", "post", "Save rule");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "postcount_tagging");
$frm->hidden("edit_rule", 2);
$frm->hidden("id", $rule['id']);

// A name for the rule. This one is purely meant as a reference to the user.
$frm->addrow(
    "Rule name / description (only for your reference)",
    $frm->text_box("name", $rule['name'], 45)
);

// Build a list of vroot folders and a javascript structure, describing
// the forums inside the vroot.
$forums = phorum_db_get_forums();
$vroot_folders = array();
$vroot2forums = array();
foreach ($forums as $forum) {
    if ($forum['forum_id'] == $forum['vroot']) {
        $vroot_folders[$forum['forum_id']] =
            addslashes(strip_tags($forum['name'])) .
            " (vroot {$forum['forum_id']})";
    } else {
        if (empty($forum['folder_flag'])) {
            $vroot2forums[$forum['vroot']][$forum['forum_id']] = $forum;
        }
    }
}
$forum_js_parts = array();
foreach ($vroot2forums as $vroot => $forums) {
    foreach ($forums as $fid => $forum) {
        if (!empty($forum['folder_flag'])) continue;
        $path = unserialize($forum['forum_path']);
        array_pop($path);
        $name = strip_tags(implode("::", array_reverse($path)));
        $vroot2forums[$vroot][$fid]["strpath"] = $name;
        $forum_js_parts[$vroot][] = "'{$forum['forum_id']}':'".addslashes($name)."'";
    }
}
$vroot_js_parts = array();
foreach($forum_js_parts as $vroot => $parts) {
    $vroot_js_parts[] = "'$vroot': {" . implode(', ', $parts) . "}";
}
$vroot_js = "{".implode(', ', $vroot_js_parts)."}";

// Create a vroot drop down menu if there are vroots available.
if (!empty($vroot_folders))
{
    $vroots = array(
        -1 => "Any vroot",
         0 => "Top level forum folder"
    );
    foreach ($vroot_folders as $vroot_folder_id => $vroot_path) {
        $vroots[$vroot_folder_id] = $vroot_path;
    }

    $frm->addrow(
        "Activate this rule for what vroot?",
        $frm->select_tag("vroot", $vroots, (int)$rule['vroot'], 'id="vroot_select" onchange="changeVroot()"')
    );
}
// Otherwise, just put in a hidden vroot variable.
else {
    $frm->hidden("vroot", 0);
    $rule['vroot'] = 0;
}

// Create a forum drop down menu to allow selecting a specific forum.
$initforums = array(-1 => "Any forum");
if (!empty($vroot2forums[$rule['vroot']])) {
    foreach ($vroot2forums[$rule['vroot']] as $f) {
        $initforums[$f['forum_id']] = $f['strpath'];
    }
}
$frm->addrow(
    "Activate this rule for what forum?",
    $frm->select_tag("forum", $initforums, (int)$rule['forum'], 'id="forum_select"')
);

// Selection to specify for what situation this rule should be active.
$frm->addrow(
    "Activate this rule for what pages?",
    $frm->checkbox('enable_profile', 1, "for user profile pages",
                   $rule['enable_profile']) . '<br/>' .
    $frm->checkbox('enable_read', 1, "for message authors on the read pages",
                   $rule['enable_read']) . '<br/>' .
    $frm->checkbox('enable_user', 1, "for the authenticated user on every page",
                   $rule['enable_user'])
);

$frm->addsubbreak("Post count criteria for this rule");

// Selection so specify what "post count" means.
if (empty($vroot_folders)) {
    $frm->addrow("The post count for a user is defined as",
        $frm->select_tag("scope", array(
            "GLOBAL" => "total number of posts in all forums",
            "FORUM"  => "number of posts in the active forum"
        ), $rule['scope'])
    );
} else {
    $frm->addrow("The post count for a user is defined as",
        $frm->select_tag("scope", array(
            "GLOBAL" => "total number of posts in all vroots",
            "VROOT"  => "number of posts in the active vroot",
            "FORUM"  => "number of posts in the active forum"
        ), $rule['scope'])
    );
}

$frm->addrow("post count larger than or equal (>=)",
             $frm->text_box(">=", $rule['>='], 8));
$frm->addrow("post count larger than (>)",
             $frm->text_box(">", $rule['>'], 8));
$frm->addrow("post count smaller than or equal (<=)",
             $frm->text_box("<=", $rule['<='], 8));
$frm->addrow("post count smaller than (<)",
             $frm->text_box("<", $rule['<'], 8));
$frm->addrow("post count equals (=)",
             $frm->text_box("=", $rule['='], 8));

$frm->addsubbreak("The HTML code to put in a template variable if the criteria are met");

$row = $frm->addrow("The template variable name to fill", $frm->text_box("tpl_var", $rule['tpl_var'], 20));
$frm->addhelp($row, "Template variable to fill",
    "If this rule matches its criteria, then a variable will be added to
     the template data. This option is used to configure the name of this
     template variable that will be added.
     <br/>
     In the examples below, we show you what template variables will be
     filled if you configure \"FOOBAR\" as the variable name to use here.<br/>
     <br/>
     <b>for the user profile page</b><br/>
     <br/>
     {PROFILE->FOOBAR}<br/>
     <br/>
     <b>for messages that are read</b><br/>
     <br/>
     all messages in a flat view read page<br/>
     {MESSAGES->USER->FOOBAR}<br/>
     <br/>
     active message in a threaded view read page<br/>
     {MESSAGE->USER->FOOBAR}<br/>
     <br/>
     the topic start message on every read page <br/>
     {TOPIC->USER->FOOBAR}<br/>
     <br/>
     <b>for the authenticated user</b><br/>
     <br/>
     {USER->FOOBAR}");

$row = $frm->addrow("HTML code to put in the template variable", $frm->textarea("tpl_html", $rule['tpl_html'], 40, 8,'style="width:95%"'));
$frm->addhelp($row, "HTML code to put in the extra template variable",
    "If this rule matches its criteria, then the HTML code that is configured
     in this option will be put in one or more template variables
     (see also the help for the previous option).<br/>
     <br/>
     You can use some special strings in the code, which will be replaced
     automatically:
     <ul>
     <li>%count% = the matching post count for the user</li>
     <li>%http_path% = the URL to the root of the Phorum install</li>
     </ul>");

$frm->show();

?>

<script type="text/javascript">
//<![CDATA[
var vroot_forums = <?php print $vroot_js ?>;

function changeVroot()
{
    var vsel = document.getElementById('vroot_select');
    var fsel = document.getElementById('forum_select');
    if (!vsel || !fsel) return;

    var vroot = vsel.options[vsel.selectedIndex].value;

    var i = 0;
    fsel.options.length = 0;

    fsel.options[i++] = new Option('Any forum', -1);

    if (vroot >= 0 && vroot_forums[vroot]) {
        for (var nr in vroot_forums[vroot]) {
            fsel.options[i++] = new Option(vroot_forums[vroot][nr], nr);
        }
    }

}

//changeVroot();
//]]>
</script>
