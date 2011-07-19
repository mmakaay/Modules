<?php

if(!defined("PHORUM")) return;

function phorum_mod_disable_bundled_jquery_register($register)
{
    // Strip the jQuery library from the javascript registry, so it
    // will not be loaded by Phorum.
    foreach ($register as $id => $entry)
    {
        if (preg_match(
          '!/jquery-\d+\.\d+\.\d+\.min\.js\)$!',
          $entry['source']
        )) {
           unset($register[$id]);
        }
    }

    // This takes care of reloading the javascript library when this
    // module script changes or when this module is disabled.
    $register[] = array(
        'module'    => 'disabled_bundled_jquery',
        'source'    => 'function(mod_disable_bundled_query_empty)',
        'cache_key' => filemtime(__FILE__)
    );

    return $register;
}

function mod_disable_bundled_query_empty()
{
    return "\n" .
           "// no code, just a stub to make the javascript\n" .
           "// library reload when needed\n" .
           "\n";
}

function phorum_mod_disable_bundled_jquery_filter($javascript)
{
    // Replace the Phorum $PJ noConflict() jQuery library reference
    // with a reference to the already loaded jQuery library.
    $javascript = preg_replace(
        '/^\s*var\s+\$PJ\s*=.*$/m',
        "if (Phorum === undefined) Phorum = { };\n" . // available in 5.3
        "var \$PJ = Phorum.jQuery = jQuery;\n",
        $javascript
    );

    return $javascript;
}

?>
