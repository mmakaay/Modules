<?php

if(!defined("PHORUM")) return;

function mod_allpagesread()
{
    if (phorum_page != 'read' && phorum_page != 'list') return;

    GLOBAL $PHORUM;
    $PHORUM["DATA"]["mod_allpagesread"]["enable"]=1;

    if(empty($PHORUM["mod_allpagesread"]["max_read_length"]) ||
       (!empty($PHORUM["read_length"]) &&
        $PHORUM["mod_allpagesread"]["max_read_length"]<$PHORUM["read_length"])){
        $PHORUM["mod_allpagesread"]["max_read_length"]=200;
    }

    // Set default value for enable_for:
    // 0 = all visitors
    // 1 = only logged in users
    if (!isset($PHORUM["mod_allpagesread"]["enable_for"])) {
        $PHORUM["mod_allpagesread"]["enable_for"] = 0;
    }


    if ($PHORUM["mod_allpagesread"]["enable_for"] == 0 ||
        $PHORUM["DATA"]["LOGGEDIN"]){
        if(phorum_page =="read"){
            if(!empty($PHORUM["args"]["page"]) &&
               $PHORUM["args"]["page"]=="all"){
                $PHORUM["read_length"] =
                    $PHORUM["mod_allpagesread"]["max_read_length"];
            }
        }
    }
    return;
}

function mod_allpagesread_createlink($data)
{
    GLOBAL $PHORUM;

    // Only do this in case flat reading mode is enabled.
    // Do not do this for a threaded list mode.
    if((phorum_page == "list" && $PHORUM["threaded_list"]) ||
       (isset($PHORUM["threaded_read"]) && $PHORUM["threaded_read"]))
       return $data;

    if ($PHORUM["mod_allpagesread"]["enable_for"] == 0 ||
        $PHORUM["DATA"]["LOGGEDIN"]){

        // The configured maxium readlength for this module.
        $maxreadlen = $PHORUM["mod_allpagesread"]["max_read_length"];

        // Handle the read page.
        if(phorum_page=="read"){

            if(isset($PHORUM["DATA"]["PAGES"][0])){

                // Determine the number of posts in this thread.
                // We might have more messages for mods.
                $thread = $data[$PHORUM["args"][1]];
                if($PHORUM["DATA"]["MODERATOR"] &&
                        isset($thread["meta"]["message_ids_moderator"])) {
                    $threadnum=count($thread["meta"]["message_ids_moderator"]);
                } else {
                    $threadnum=$thread["thread_count"];
                }

                // Only show the All link, in case the number of
                // messages in the thread is below the configured maximum.
                if ($threadnum > $maxreadlen) return $data;

                // Add the All link to the paging.
                $newpage_index=count($PHORUM["DATA"]["PAGES"]);
                $PHORUM["DATA"]["PAGES"][$newpage_index]["pageno"] = $PHORUM["DATA"]["LANG"]["mod_allpagesread"]["all_page_link"];
                $PHORUM["DATA"]["PAGES"][$newpage_index]["url"] = str_replace("page=1", "page=all", $PHORUM["DATA"]["PAGES"][0]["url"]);
            }

            // Handle the list page.
        } else {

            foreach ($data as $key=>$value) {
                if(!empty($value["pages"])) {

                    // Determine the number of posts in this thread.
                    // We might have more messages for mods.
                    if($PHORUM["DATA"]["MODERATOR"] &&
                            isset($value["meta"]["message_ids_moderator"])) {
                        $threadnum=count($value["meta"]["message_ids_moderator"]);
                    } else {
                        $threadnum=$value["thread_count"];
                    }

                    // Only show the All link, in case the number of
                    // messages in the thread is below the configured maximum.
                    if ($threadnum > $maxreadlen) continue;

                    // Add the All link to the paging.
                    $read_url = isset($value["url"])
                        ? $value["url"]          // Phorum 5.0.x
                        : $value["URL"]["READ"]; // Phorum 5.1.x
                    $data[$key]["pages"].=" <a href=\"$read_url,page=all\">".$PHORUM["DATA"]["LANG"]["mod_allpagesread"]["all_page_link"]."</a>";
                    $data[$key]["pages"]=str_replace(",&nbsp;"," ",$data[$key]["pages"]);
                }
            }

        }
    }

    return $data;
}

?>
