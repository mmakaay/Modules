<?php
if(!defined("PHORUM")) return;

require_once("./mods/banner_manager/defaults.php");
require_once("./mods/banner_manager/render.php");

// Find out what block we want to display on this page.
function phorum_mod_banner_manager_common()
{
    $settings = $GLOBALS["PHORUM"]["mod_banner_manager"];

    switch(phorum_page) {
        case "pm"       : $page_id = "pm";       break;
        case "login"    : $page_id = "login";    break;
        case "register" : $page_id = "register"; break;
        case "profile"  : $page_id = "profile";  break;
        case "control"  : $page_id = "control";  break;
        default         : $page_id = $GLOBALS["PHORUM"]["forum_id"];
    }

    // Find out what banner we want to display on the current page.
    $banner_id = NULL;
    if (isset($settings["links"][$page_id]))
    {
        $type = $settings["links"][$page_id];

        // No banner for the current page.
        if ($type == "NULL") {
            $banner_id = NULL;
        }

        // A specific banner for the current page.
        elseif (preg_match('/^BANNER:(.+)$/', $type, $m)) {
            $banner_id = $m[1];
        }

        // A random and/or date based banner.
        elseif (($type == "RANDOM") ||
                ($type == "RANDOM_INC_DATES") ||
                 $type == "DATES")
        {
            // Multiple banners could be applicable. Build a list
            // of candidate banners, which we can use later to pick
            // a random banner.
            $banner_candidates = array();

            // Select the banners that are applicable.
            foreach($settings["banners"] as $thisBanner)
            {
                // No date has been set for the banner.
                if (empty($thisBanner["startDay"]))
                {
                    // Only add the banner if the current page accepts
                    // random banners.
                    if (($type == "RANDOM") ||
                        ($type == "RANDOM_INC_DATES")) {
                        $banner_candidates[] = $thisBanner["id"];
                    }

                    continue;
                }

                // A date has been set for the banner. We'll only include
                // it in the list if the date matches.

                // First of all, never use date based banners when the RANDOM
                // setting is used. Only if RANDOM_INC_DATES or DATES is used,
                // we have to include date based banners.
                if ($type == "RANDOM") continue;

                // Start year is optional. Use the current year as fallback.
                $startYear  = empty($thisBanner['startYear'])
                            ? date("Y") : (int)$thisBanner["startYear"];

                $startDay   = (int)$thisBanner["startDay"];
                $startMonth = (int)$thisBanner["startMonth"];
                $endDay     = (int)$thisBanner["endDay"];
                $endMonth   = (int)$thisBanner["endMonth"];
                $endYear    = (int)$thisBanner["endYear"];

                // If the user didn't provide an end year, then calculate it.
                if (empty($endYear))
                {
                    // assume this year to start with
                    $endYear = date("Y");

                    // month spans December 31st
                    if ($endMonth < $startMonth) $endYear++;

                    // day plus month spans December 31st
                    else if (($endMonth == $startMonth) && $endDay < $startDay)
                        $endYear++;
                }

                $tsStart = mktime(0, 0, 0, $startMonth, $startDay, $startYear);
                // No point continuing if start date is invalid.
                if (!$tsStart) break;

                // Note: we are using an end time of 23:59:59, so 1 day
                // spans are possible (from 00:00:00 - 23:59:59).
                $tsEnd = mktime(23,59,59,$endMonth,$endDay,$endYear);
                // No point continuing if end date is invalid.
                if (!$tsEnd) break;

                // get current timestamp
                $tsCurrent = mktime();

                // If the current timestamp is within the banner date range,
                // then add the banner to the list of candidate banners.
                if (($tsStart <= $tsCurrent) && ($tsEnd >= $tsCurrent)) {
                    $banner_candidates[] = $thisBanner["id"];
                }
            }

            // Did we find any banners?
            if (!empty($banner_candidates)) {
                // If so, grab one at random.
                $banner_id = $banner_candidates[array_rand($banner_candidates)];
            }
        }

        // Paranoia.
        else trigger_error(
            'Illegal banner type "'.$type.'" used in banner manager ' .
            'for page "'.$page_id.'"',
            E_USER_ERROR
        );
    }

    // Retrieve the banner data and build the formatted block for the page.
    $block = '';
    if ($banner_id !== NULL && isset($settings["banners"][$banner_id])) {
        $banner = $settings["banners"][$banner_id];
        $block = phorum_mod_banner_manager_render($banner);
    }

    $GLOBALS["PHORUM"]["DATA"]["MOD_BANNER_MANAGER"] = $block;
}

function phorum_mod_banner_manager_after_header(){
    phorum_mod_banner_manager_automatic_display("after_header");
}

function phorum_mod_banner_manager_before_footer(){
    phorum_mod_banner_manager_automatic_display("before_footer");
}

function phorum_mod_banner_manager_automatic_display($hook)
{
    $settings = $GLOBALS["PHORUM"]["mod_banner_manager"];

    // We're done if we don't want to display the banner in the current hook.
    if ($settings["banner_position"] != $hook) return;

    // We're done if we don't want to display the banner automatically.
    if ($settings["hide_banner"]) return;

    print "<div align=\"{$settings["banner_alignment"]}\">";
    print $GLOBALS["PHORUM"]["DATA"]["MOD_BANNER_MANAGER"];
    print "</div>";
}
?>
