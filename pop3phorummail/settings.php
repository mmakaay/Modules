<?php
// parts of these settings page are coming from the mailinglist module by Oliver Riesen
// especially the functions at the bottom, images and the general layout


// Make sure that this script is loaded from the admin interface.
if (!defined('PHORUM_ADMIN')) return;

// Save settings in case this script is run after posting
// the settings form.
if (    count($_POST)
     && isset($_POST['forum_pop3servers']) ) {
    // Create the settings array for this module.
    $PHORUM['mod_pop3phorummail'] = array
        ( 
          'forum_pop3servers' => $_POST['forum_pop3servers'],
        );

    if (!phorum_db_update_settings(array('mod_pop3phorummail'=>$PHORUM['mod_pop3phorummail']))) {
        $error = 'Database error while updating settings.';
    } else {
        phorum_admin_okmsg('Settings Updated');
    }
}

// We build the settings form by using the PhorumInputForm object.
include_once './include/admin/PhorumInputForm.php';
$frm = new PhorumInputForm('', 'post', 'Save settings');
$frm->hidden('module', 'modsettings');
$frm->hidden('mod', 'pop3phorummail');

// Here we display an error in case one was set by saving
// the settings before.
if (!empty($error)){
    phorum_admin_error($error);
}

$frm->addbreak('Edit Settings for the POP3 Phorummail Module');

$row = $frm->addbreak('Define POP3 Servers for the individual forums');


$tree = phorum_mod_pop3phorummail_getforumtree();
$forumlist = array();
foreach ($tree as $data) {
    $level = $data[0];
    $node = $data[1];
    $name = str_repeat('&nbsp;&nbsp;', $level);
    $name .= '<img border="0" src="'.$PHORUM['http_path'].'/mods/pop3phorummail/images/'
               .($node['folder_flag'] ? 'folder.gif' : 'forum.gif').'" /> ';
    $name .= $node['name'];

    if ($node['folder_flag']) {
        // No settings for folders.
        $frm->addrow($name);
    } else {
    	$nodesettings = $PHORUM['mod_pop3phorummail']['forum_pop3servers'][$node['forum_id']];
    	    if(!empty($nodesettings['hostname'])) {
    	    	$hostname = $nodesettings['hostname'];
    	    } else {
    	    	$hostname = '';
    	    }
    	    
            if(!empty($nodesettings['username'])) {
                $username = $nodesettings['username'];
            } else {
                $username = '';
            }

            if(!empty($nodesettings['password'])) {
                $password = $nodesettings['password'];
            } else {
                $password = '';
            } 

            if(!empty($nodesettings['enabled'])) {
                $enabled = $nodesettings['enabled'];
            } else {
                $enabled = 0;
            }                    
    	
            $frm->addrow($name);
            $frm->addrow
                ( str_repeat('&nbsp;&nbsp;', $level + 1).'Enabled',
                  $frm->checkbox('forum_pop3servers['.$node['forum_id'].'][enabled]',1,'Yes',$enabled)
                  );
                             
            $frm->addrow
                ( str_repeat('&nbsp;&nbsp;', $level + 1).'POP3 Host',
                  $frm->text_box
                      ( 'forum_pop3servers['.$node['forum_id'].'][hostname]',
                        $hostname,
                        40 ) );
            $frm->addrow
                ( str_repeat('&nbsp;&nbsp;', $level + 1).'POP3 Username',
                  $frm->text_box
                      ( 'forum_pop3servers['.$node['forum_id'].'][username]',
                        $username,
                        40 ) );
            $frm->addrow
                ( str_repeat('&nbsp;&nbsp;', $level + 1).'POP3 Password',
                  $frm->text_box
                      ( 'forum_pop3servers['.$node['forum_id'].'][password]',
                        $password,
                        40 ) );                        
        
    }
}
// Show settings form
$frm->show();

//
// Internal functions
//

function phorum_mod_pop3phorummail_getforumtree() {
    // Retrieve all forums and create a list of all parents
    // with their child nodes.
    $forums = phorum_db_get_forums();
    $nodes = array();
    foreach ($forums as $id => $data) {
        $nodes[$data['parent_id']][$id] = $data;
    }

    // Create the full tree of forums and folders.
    $treelist = array();
    phorum_mod_pop3phorummail_mktree(0, $nodes, 0, $treelist);
    return $treelist;
}

// Recursive function for building the forum tree.
function phorum_mod_pop3phorummail_mktree($level, $nodes, $node_id, &$treelist) {
    // Should not happen but prevent warning messages, just in case...
    if (!isset($nodes[$node_id])) return;

    foreach ($nodes[$node_id] as $id => $node) {

        // Add the node to the treelist.
        $treelist[] = array($level, $node);

        // Recurse folders.
        if ($node['folder_flag']) {
            $level++;
            phorum_mod_pop3phorummail_mktree($level, $nodes, $id, $treelist);
            $level--;
        }
    }
}

?>
