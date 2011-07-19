<?php

if (!defined('PHORUM')) return;

define('YUICOMPRESSOR_VERSION', '2.4.2');

function compress_yui_empty () { return ''; }

function phorum_mod_compress_yui_javascript_register($data)
{
    global $PHORUM;

    // We need to force rebuilding the cache if this module is enabled
    // or if the module version is changed.
    $data[] = array(
        'module'    => 'compress_yui',
        'source'    => 'function(compress_yui_empty)',
        'cache_key' => filemtime(__FILE__)
    );

    return $data;
}

function phorum_mod_compress_yui_javascript_filter($javascript)
{
    global $PHORUM;

    // In safe mode, we will run into troubles with forking java.
    // Therefore, we return right away in that case.
    if (get_cfg_var('safe_mode')) {
        return "// Compression using yuicompressor failed!\n" .
               "// PHP has \"safe_mode\" enabled on this server.\n" .
               $javascript;
    }

    // Work arounds for known compression problems.
    // * "char" is a reserved word, but got used as a var name in the
    //   bbcode editor tools code (fixed in bbcode mod code as well).
    $javascript = str_replace(
        array(
            'var char = str[i];',
            'char == \'\\\\\' || char == \'"\'',
            'quoted += char;'
        ),
        array(
            'var c = str[i]',
            'c = \'\\\\\' || c == \'"\'',
            'quoted += c;'
        ),
        $javascript
    );

    // Build the command that we have to run.
    $java = empty($PHORUM['mod_compress_yui']['java_path'])
          ? 'java' : $PHORUM['mod_compress_yui']['java_path'];
    $java = escapeshellcmd($java);
    $jar  = escapeshellarg(dirname(__FILE__) .
            '/yuicompressor-'.YUICOMPRESSOR_VERSION.'.jar');
    $cmd  = "$java -jar $jar --type js";

    // Try to fork the java process. Return on errors.
    $descriptors = array(
        0 => array('pipe', 'r'),
        1 => array('pipe', 'w'),
        2 => array('pipe', 'w')
    );
    $proc = proc_open($cmd, $descriptors, $pipes);
    if (!is_resource($proc)) {
        return $javascript;
    }

    // Feed the javascript to the compressor.
    fwrite($pipes[0], $javascript);
    fclose($pipes[0]);

    // Read back the (hopefully) compressed javascript.
    $compressed = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    // Check for errors.
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    // Close the process.
    $exit = proc_close($proc);

    // Compression was succesful! :-)
    if ($exit == 0 and $errors == '') {
        return "// This file was compressed by yuicompressor\n" .
               $compressed;
    }

    // Compression failed :-(
    // Add the error output as comments to the output.
    $jserrors = "//" . str_repeat('-', 72) . "\n" .
                "// Compression using yuicompressor failed!\n" .
                "//\n" .
                "// EXIT CODE: $exit\n\n";
    foreach (explode("\n", "STDERR:\n$errors\n\nSTDOUT:\n$compressed") as $line)
    {
        $jserrors .= "// $line\n";
    }
    $jserrors .= "//" . str_repeat('-', 72) . "\n\n";

    return $jserrors . $javascript;
}

?>
