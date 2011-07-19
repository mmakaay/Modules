<?php

require_once("./include/timing.php");
timing_start();

function phorum_mod_devel_timing_common_pre()
{
    ob_start();
}

function phorum_mod_devel_timing_common()
{
    timing_mark("end of hook common");
}

function phorum_mod_devel_timing_index($data)
{
    timing_mark("end of hook index");
    return $data;
}

function phorum_mod_devel_timing_list($data)
{
    timing_mark("end of hook list");
    return $data;
}

function phorum_mod_devel_timing_read($data)
{
    timing_mark("end of hook read");
    return $data;
}

function phorum_mod_devel_timing_format($data)
{
    timing_mark("end of hook format");
    return $data;
}

function phorum_mod_devel_timing_start_output()
{
    timing_mark("start of hook start_output");
}

function phorum_mod_devel_timing_end_output()
{
    timing_mark("end of hook end_output");

    $page = ob_get_contents();
    ob_end_clean();

    ob_start();
    timing_print();
    $timing = ob_get_contents();
    ob_end_clean();

    $timing = "
         <div style=\"background-color: #f0f0f0;
                      color: black;
	              border-bottom: 2px solid black;
		      padding: 10px;
                      font-size: 9px;
                      text-align: center\">
           <center>
             <b>Development timing statistics:</b><br/><br/>" .
             $timing .
          "</center>
         </div>";

    $page = preg_replace('/(<body[^>]*>)/', '\1'.$timing, $page);

    print $page;
}

?>
