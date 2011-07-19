<?php

// Commit the current block.
if (isset($_POST["ok"]))
{
    $banners[$_POST["edit"]]["block"]      = $_POST["block"];
    $banners[$_POST["edit"]]["startDay"]   = $_POST["startDay"];
    $banners[$_POST["edit"]]["startMonth"] = $_POST["startMonth"];
    $banners[$_POST["edit"]]["startYear"]  = $_POST["startYear"];
    $banners[$_POST["edit"]]["endDay"]     = $_POST["endDay"];
    $banners[$_POST["edit"]]["endMonth"]   = $_POST["endMonth"];
    $banners[$_POST["edit"]]["endYear"]    = $_POST["endYear"];
    $banners[$_POST["edit"]]["timestamp"]  = time();
    banner_manager_save_settings();
    unset($_POST);
    include("settings_banners.php");
    return;
}


// Cancel editing. Remove the banner in case we are editing
// a new banner.
if (isset($_POST["cancel"])) {
    if ($edit_type == 'add') {
        unset($banners[$_POST["edit"]]);
        banner_manager_save_settings();
    }
    unset($_POST);
    include("settings_banners.php");
    return;
}

// Display a preview of the current banner block.
if (isset($_POST["preview"])) {
    print "<h3>Block preview:</h3>";
    $data = $banners[$_POST["edit"]];
    $data["block"]      = $_POST["block"];
    $data["startDay"]   = $_POST["startDay"];
    $data["startMonth"] = $_POST["startMonth"];
    $data["startYear"]  = $_POST["startYear"];
    $data["endDay"]     = $_POST["endDay"];
    $data["endMonth"]   = $_POST["endMonth"];
    $data["endYear"]    = $_POST["endYear"];
    $data["timestamp"]  = time();
    print "<div style=\"border: 1px solid black; padding: 10px\">";
    print phorum_mod_banner_manager_render($data, true);
    print "</div>";
}

foreach (array('block', 'startDay', 'startMonth', 'startYear',
               'endDay', 'endMonth', 'endYear') as $field) {
    if (! isset($_POST[$field])) $_POST[$field] = $banner[$field];
}
?>

<form style="display:inline" action="admin.php" method="post">
<input type="hidden" name="module" value="modsettings"/>
<input type="hidden" name="mod" value="banner_manager"/>
<input type="hidden" name="panel" value="banners"/>
<input type="hidden" name="edit" value="<?php print htmlspecialchars($banner["id"])?>"/>
<input type="hidden" name="type" value="<?php print $edit_type?>"/>
<input type="hidden" name="phorum_admin_token"
       value="<?php print $PHORUM['admin_token'] ?>"/>

<?php
if ($edit_type == 'edit') {
    print "<h3>Edit banner block: ";
} else {
    print "<h3>Add new banner block: ";
}
print htmlspecialchars($banner["name"]);
print "</h3>";
?>

Here, you can enter the data for the block. This data will be
PHP included in the Phorum pages. This means that you can use
HTML, PHP or a combination of both to define your blocks.
If you are using PHP code, then make sure that the code is
running correctly by requesting a preview before committing it.
<br/><br/>

<b>Enter the block code:</b><br/>
<textarea name="block" cols="70" rows="10" style="width:95%"><?php print htmlspecialchars($_POST["block"]) ?></textarea>
<br/>
<p>
  <b>(optional) Only show this banner between the following dates:</b><br/>
  <small>
    (Leave the year blank to handle recurring days; the month must be set)
  </small>
</p>
<p>
  Beginning:
  <?php
  outputDayDropdown("startDay",$_POST["startDay"]);
  outputMonthDropdown("startMonth",$_POST["startMonth"]);
  outputYearDropdown("startYear",$_POST["startYear"]);
  ?>
  &nbsp; &nbsp;
  Up to and including:
  <?php
  outputDayDropdown("endDay",$_POST["endDay"]);
  outputMonthDropdown("endMonth",$_POST["endMonth"]);
  outputYearDropdown("endYear",$_POST["endYear"]);
  ?>
</p>
<br/>

<input type="submit" name="preview" value="  Preview  ">
<input type="submit" name="ok" value="  OK  ">
<input type="submit" name="cancel" value="  Cancel  ">

</form>

<?php
function outputDayDropdown($controlName, $selectedValue){
    echo "<select name=\"$controlName\">\n";
    echo "<option value=\"\"></option>\n";
    for ($i=1; $i<=31; $i++){
        $selected = ($i == $selectedValue) ? "selected" : "";
        echo "<option value=\"$i\" $selected>$i</option>\n";
    }
    echo "</select>\n";
}

function outputMonthDropdown($controlName,$selectedValue){
    echo "<select name=\"$controlName\">\n";
    echo "<option value=\"\"></option>\n";
    for ($i=1; $i<=12; $i++){
        $selected = ($i == $selectedValue) ? "selected" : "";
        $thisMonth = date("F", mktime(0, 0, 0, $i, 1, 0, 0));
        echo "<option value=\"$i\" $selected>$thisMonth</option>\n";
    }
    echo "</select>\n";
}

function outputYearDropdown($controlName,$selectedValue){
    echo "<select name=\"$controlName\">\n";
    echo "<option value=\"\"></option>\n";
    for ($i=0; $i<=4; $i++){
        $thisYear = date("Y") + $i;
        $selected = ($thisYear == $selectedValue) ? "selected" : "";
        echo "<option value=\"$thisYear\" $selected>$thisYear</option>\n";
    }
    echo "</select>\n";
}
?>
