<?php

if (!defined('PHORUM')) return;

define('MINIFY_VERSION', '2.1.2');
define('MINIFY_PATH', './mods/compress_minify/minify-'.MINIFY_VERSION);

function compress_minify_empty() { return ''; }

function phorum_mod_compress_minify_javascript_register($data)
{
    // We need to force rebuilding the cache if this module is enabled
    // or if the module version is changed.
    $data[] = array(
        'module'    => 'compress_minify',
        'source'    => 'function(compress_minify_empty)',
        'cache_key' => filemtime(__FILE__)
    );

    return $data;
}

function phorum_mod_compress_minify_js($javascript)
{
    require_once(MINIFY_PATH . '/JSMin.php');
    return JSMin::minify($javascript);
}

function phorum_mod_compress_minify_css_register($data)
{
    // We need to force rebuilding the cache if this module is enabled
    // or if the module version is changed.
    $data['register'][] = array(
        'module'    => 'compress_minify',
        'where'     => 'after',
        'source'    => 'function(compress_minify_empty)',
        'cache_key' => filemtime(__FILE__)
    );
    return $data;
}

function phorum_mod_compress_minify_css($css)
{
    require_once(MINIFY_PATH . '/CSS.php');
    return  Minify_CSS::minify($css, array(
        'preserveComments' => FALSE
    ));
}

function phorum_mod_compress_minify_html()
{
    ob_start('phorum_mod_compress_minify_html_finish');
}

function phorum_mod_compress_minify_html_finish($html)
{
    require_once(MINIFY_PATH . '/HTML.php');
    return  Minify_HTML::minify($html, array(
        'cssMinifier' => 'phorum_mod_compress_minify_css',
        'jsMinifier'  => 'phorum_mod_compress_minify_js'
    ));
}

?>
