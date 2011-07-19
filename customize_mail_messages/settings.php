<?php
    if(!defined("PHORUM_ADMIN")) return;

    // The definition of the mail messages that we can customize.
    $msgdef = array(
      "pm_notify" => array (
        "type"    => "User: new private message received",
        "subject" => "PMNotifySubject",
        "body"    => "PMNotifyMessage",
        "fields"  => array (
          "%author%"     => "The name of the author of the message.",
          "%subject%"    => "The subject of the message.",
          "%full_body%"  => "The full body of the message including all " .
                            "HTML/BBCode tags, use with care!",
          "%plain_body%" => "The body of the message, stripped from all " .
                            "HTML/BBCode tags. This is the body text that " .
                            "you would normally include in a mail message.",
          "%read_url%"   => "The url where this message can be read.",
        )
      ),
      "follow" => array (
        "type"    => "User: new message in a followed thread",
        "subject" => "NewReplySubject",
        "body"    => "NewReplyMessage",
        "fields"  => array (
          "%forumname%"  => "The name of the forum in which the message " .
                            "was posted.",
          "%author%"     => "The name of the author of the message.",
          "%subject%"    => "The subject of the message.",
          "%full_body%"  => "The full body of the message including all " .
                            "HTML/BBCode tags, use with care!",
          "%plain_body%" => "The body of the message, stripped from all " .
                            "HTML/BBCode tags. This is the body text that " .
                            "you would normally include in a mail message.",
          "%read_url%"   => "The url where this message can be read.",
          "%remove_url%" => "The url to unsubscribe from the followed thread.",
          "%noemail_url%"=> "The url to set the subscription to \"no email\",".
                            " so the thread is only followed from the user's ".
                            "control center.",
          "%followed_threads_url%" => "The url to the control center page " .
                            "where the user can look at all the followed " .
                            "threads."
        )
      ),
      "moderator_new_approve" => array (
        "type"    => "Moderator: new message which does need approval",
        "subject" => "NewModeratedSubject",
        "body"    => "NewModeratedMessage",
        "fields"  => array (
          "%forumname%"  => "The name of the forum in which the message " .
                            "was posted.",
          "%author%"     => "The name of the author of the message.",
          "%subject%"    => "The subject of the message.",
          "%full_body%"  => "The full body of the message including all " .
                            "HTML/BBCode tags, use with care!",
          "%plain_body%" => "The body of the message, stripped from all " .
                            "HTML/BBCode tags. This is the body text that " .
                            "you would normally include in a mail message.",
          "%read_url%"   => "The url where this message can be read.",
          "%approve_url%"=> "The url where this message can be approved or " .
                            "disapproved."
        )
      ),
      "moderator_new_noapprove" => array (
        "type"    => "Moderator: new message which does not need approval",
        "subject" => "NewModeratedSubject",
        "body"    => "NewUnModeratedMessage",
        "fields"  => array (
          "%forumname%"  => "The name of the forum in which the message " .
                            "was posted.",
          "%author%"     => "The name of the author of the message.",
          "%subject%"    => "The subject of the message.",
          "%full_body%"  => "The full body of the message including all " .
                            "HTML/BBCode tags, use with care!",
          "%plain_body%" => "The body of the message, stripped from all " .
                            "HTML/BBCode tags. This is the body text that " .
                            "you would normally include in a mail message.",
          "%read_url%"   => "The url where this message can be read."
        )
      ),
      "moderator_report" => array(
        "type"    => "Moderator: reported message",
        "subject" => "ReportPostEmailSubject",
        "body"    => "ReportPostEmailBody",
        "fields"  => array (
          "%forumname%"  => "The name of the forum in which the message " .
                            "was posted.",
          "%author%"     => "The name of the author of the message.",
          "%subject%"    => "The subject of the message.",
          "%body%"       => "The body of the message.",
          "%url%"        => "The url where this message can be read.",
          "%reportedby%" => "The username of the reporter.",
          "%reporter_url%"=> "The url for the reporter's profile.",
          "%explanation%"=> "The explanation given by the reporter.",
          "%ip%"         => "The IP-address of the reporter.",
          "%date%"       => "The date of reporting.",
          "%delete_url%" => "The url to delete the message.",
          "%hide_url%"   => "The url to disapprove and hide the message.",
          "%edit_url%"   => "The url to edit the message."
        )
      )
    );

    $languages = phorum_get_language_info();

    // Easy access to the config array.
    if (!isset($GLOBALS["PHORUM"]["mod_customize_mail_messages"]) ||
        !is_array($GLOBALS["PHORUM"]["mod_customize_mail_messages"])) {
        $GLOBALS["PHORUM"]["mod_customize_mail_messages"] = array();
    }
    $config =& $GLOBALS["PHORUM"]["mod_customize_mail_messages"];

    // save settings
    if(isset($_POST['typeselect']) && isset($_POST['langselect']) &&
       isset($msgdef[$_POST['typeselect']])) {

        $def = $msgdef[$_POST['typeselect']];
        $lang = $_POST['langselect'];
        $id = $_POST['typeselect'] . ":" . $_POST['langselect'];
        if (isset($_POST["$id:subject"]) && trim($_POST["$id:subject"]) != '') {
            $config[$lang][$def['subject']] = trim($_POST["$id:subject"]);
        } else {
            unset($config[$lang][$def['subject']]);
        }
        if (isset($_POST["$id:body"]) && trim($_POST["$id:body"]) != '') {
            $config[$lang][$def['body']] = trim($_POST["$id:body"]);
        } else {
            unset($config[$lang][$def['body']]);
        }
        if (isset($_POST["$id:mailfrom"]) && trim($_POST["$id:mailfrom"]) != '') {
            $config[$lang][$def['body']."_mailfrom"] = trim($_POST["$id:mailfrom"]);
        } else {
            unset($config[$lang][$def['mailfrom']."_mailfrom"]);
        }

        if(!phorum_db_update_settings(array("mod_customize_mail_messages" => $config))) {
            phorum_admin_error("Database error while updating settings");
        } else {
            phorum_admin_okmsg("Settings updated");
        }
    }

    include_once "./include/admin/PhorumInputForm.php";
    $frm =& new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "customize_mail_messages");

    $frm->addbreak("Customize Mail Messages");

    $frm->addmessage("
        <div style=\"font-size: 10px\">
        Here, you can customize the mail messages that Phorum sends out.
        Select the type of message and the language, edit the subject
        and / or message body and hit the \"Save\" button to store the
        custom mail message. To use the default subject or body (as defined
        in the language file), simply empty the form field. Below the form,
        you will find the variables that can be used in the mail. 
        </div>
    ");

    ob_start(); ?>

    <div id="forms"> <?php

    $typeselect = array();
    foreach ($msgdef as $id => $conf)
    {
      $typeselect[$id] = $conf["type"];

      foreach ($languages as $lid => $lconf)
      { ?>
        <div id="form_<?php print $id."_".$lid ?>"
             style="display:none">
          <strong>Message subject</strong>
          (language file key: <?php print $conf['subject'] ?>)<br/>
          <input type="text" name="<?php print $id.":".$lid?>:subject" 
                 value="<?php print isset($config[$lid][$conf['subject']]) ? htmlspecialchars($config[$lid][$conf['subject']]) : "" ?>" size="50" />
          <br/><br/>
          <strong>Message body</strong>
          (language file key: <?php print $conf['body'] ?>)<br/>
          <textarea name="<?php print $id.":".$lid ?>:body"
                    cols="70" rows="7"><?php print isset($config[$lid][$conf['body']]) ? htmlspecialchars($config[$lid][$conf['body']]) : "" ?></textarea>
          <br/><br/>
          <strong>Mail address for the message sender (From:)</strong><br/>
          <input type="text" name="<?php print $id.":".$lid?>:mailfrom"
                 value="<?php print isset($config[$lid][$conf['body']."_mailfrom"]) ? htmlspecialchars($config[$lid][$conf['body']."_mailfrom"]) : "" ?>" size="50" />
        </div> <?php
      }

      print "<div id=\"varlist_$id\">";
      print "<br/>";
      print "<table border=\"1\" cellpadding=\"5\" cellspacing=\"0\">";
      print "<tr><th colspan=\"2\">Available mail template variables</th></tr>";
      foreach ($conf['fields'] as $fld => $info) {
          print "<tr>";
          print "<td valign=\"top\">$fld</td>";
          print "<td valign=\"top\">$info</td>";
          print "</tr>";
      }
      print "</table>";
      print "</div>";
    } ?>
    </div>
    <?php

    $forms = ob_get_contents();
    ob_end_clean();

    $frm->addrow("Type of mail message", $frm->select_tag("typeselect", $typeselect, $_POST["typeselect"], 'id="typeselect" onchange="toggleType()"'));
    $frm->addrow("Language", $frm->select_tag("langselect", $languages, $_POST['langselect'], 'id="langselect" onchange="toggleType()"'));

    $row = $frm->addmessage($forms);

    $frm->show();
?>

<script type="text/javascript">
//<![CDATA[
function toggleType()
{
   var t = document.getElementById('typeselect');
   var sel = t.selectedIndex;
   var type = t.options[sel].value;

   var l = document.getElementById('langselect');
   var sel = l.selectedIndex;
   var lang = l.options[sel].value;

   var id = 'form_' + type + "_" + lang;
   var vid = 'varlist_' + type;

   var c = document.getElementById('forms');
   var d = c.getElementsByTagName('div');
   for (var i = 0; i < d.length; i++) {
       d[i].style.display = (id == d[i].id || vid == d[i].id) 
                          ? 'block' : 'none';
   }
}

// Initialize display.
toggleType();

//]]>
</script>
