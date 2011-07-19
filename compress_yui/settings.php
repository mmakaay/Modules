<?php

if (!defined("PHORUM_ADMIN")) return;

if (get_cfg_var('safe_mode')) {
    phorum_admin_error(
        'The PHP option "safe_mode" is enabled on this server.<br/>' .
        'This will keep this module from functioning.'
    );
}

// Backward compatibility.
if (isset($PHORUM['mod_compress_javascript']['java_path']) &&
    !isset($PHORUM['mod_compress_yui']['java_path'])) {
    $PHORUM['mod_compress_yui']['java_path'] =
        $PHORUM['mod_compress_javascript']['java_path'];
}

if (count($_POST))
{
    $errors = 0;
    $PHORUM["mod_compress_yui"] = array(
        'java_path' => trim($_POST['java_path'])
    );

    if ($PHORUM['mod_compress_yui']['java_path'] != '')
    {
        $java = escapeshellcmd($PHORUM['mod_compress_yui']['java_path']);

        if (!file_exists($java)) {
            phorum_admin_error(
              "The provided java path does not point to an existing file"
            );
            $errors ++;
        }

        if (!$errors)
        {
            $pipe = popen("$java -version 2>&1", "r");
            if (!is_resource($pipe)) {
                phorum_admin_error(
                 'The provided java path is wrong, or the PHP config ' .
                 'disallows execution of the java program (e.g. by means ' .
                 'of the "safe_mode" php.ini setting)'
                );
                $errors ++;
            } else {
                $read = stream_get_contents($pipe);
                if (strstr($read, "java") === FALSE) {
                    phorum_admin_error(
                      'The provided java path does not seem to point to the' .
                      'java program (-version output did not contain "java")'
                    );
                    $errors ++;
                } else {
                    $exit = pclose($pipe);
                    if ($exit != 0) {
                        phorum_admin_error(
                          'The java path does not point to the java program. '.
                          'When running the program with the ' .
                          '\"-version\" option, it did not return ' .
                          'exit code 0.'
                        );
                    }
                }
            }
        }
    }

    if (!$errors) {
        phorum_db_update_settings(array(
            'mod_compress_yui' => $PHORUM['mod_compress_yui']
        ));
        phorum_admin_okmsg('Settings successfully saved');
    }
}

// Check if the "java" program can be found in the system path.
$found = NULL;
$path = getenv('PATH');
if ($path && strlen($path)) {
    foreach(explode(':', $path) as $dir) {
        if (@file_exists("$dir/java") && @is_executable("$dir/java")) {
            $found = "$dir/java";
            break;
        }
    }
}
$found_notice = '';
if ($found) {
    $found_notice =
        "This module was able to find the java program at the location " .
        htmlspecialchars($found) . ". If you want to use a specific " .
        "java installation for this module, you can provide the full " .
        "path to the \"java\" program below. If you want to use " .
        "the autodetected path, then you can leave the entry empty.";
} else {
    $found_notice =
        "This module was unable to automatically find the java program " .
        "on the system. This might indicate that java is not installed " .
        "at all on the system or that it is installed in a non-standard " .
        "location. If you know where java is installed, you can provide " .
        "the full path to the \"java\" program below. If you are not " .
        "sure, then check with your system administrator.";
}

require_once('./include/admin/PhorumInputForm.php');
$frm = new PhorumInputForm ('', 'post', 'Save');
$frm->hidden('module', 'modsettings');
$frm->hidden('mod', 'compress_yui');

$frm->addbreak('Compress Javascript Module Settings');

$frm->addmessage(
    'For compressing the Phorum JavaScript code, this module uses ' .
    '<a href="http://developer.yahoo.com/yui/compressor/">' .
    'Yahoo\'s Java based YUI Compressor</a>. To be able to run this ' .
    'module, the webserver must have Java (version >= 1.4) installed.<br/>' .
    '<br/>' .  $found_notice
);

$frm->addrow(
    'Provide the path to "java" or leave empty for autodetection',
    $frm->text_box(
        'java_path',
        $PHORUM['mod_compress_yui']['java_path'],
        30, 255
    )
);

$frm->show();
?>
