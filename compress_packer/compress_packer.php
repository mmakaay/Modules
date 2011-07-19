<?php

if (!defined('PHORUM')) return;

define('PACKER_VERSION', '1.1');

function compress_packer_empty () { return ''; }

function phorum_mod_compress_packer_javascript_register($data)
{
    global $PHORUM;

    // We need to force rebuilding the cache if this module is enabled
    // or if the module version is changed.
    $data[] = array(
        'module'    => 'compress_packer',
        'source'    => 'function(compress_packer_empty)',
        'cache_key' => filemtime(__FILE__)
    );

    return $data;
}

function phorum_mod_compress_packer_javascript_filter($javascript)
{
    $lib = './mods/compress_packer/packer-'.PACKER_VERSION.
           '/class.JavaScriptPacker.php';
    $phpversion = phpversion();
    if ($phpversion[0] == '4') $lib .= '4';

    require_once($lib);
    $packer = new JavaScriptPacker($javascript);
    return $packer->pack();
}

?>
