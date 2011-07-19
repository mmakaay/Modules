<?php
    $banners =& $GLOBALS["PHORUM"]["mod_banner_manager"]["banners"];

    // Check if edit or delete mode is requested.
    $do_edit = null;
    $edit_type = 'add';
    if (isset($_POST["edit"])) {
        $edit_type = 'edit';
        $do_edit = $_POST["edit"];
    }
    if ($do_edit == null && isset($_POST)) {
        foreach ($_POST as $k => $v) {
            if (preg_match('/^edit:(.+)$/', $k, $m)) {
                $edit_type = 'edit';
                $do_edit = $m[1];
                break;
            }
            // Delete mode? Then we're done. Delete the banner and continue.
            if (preg_match('/^delete:(.+)$/', $k, $m)) {
                unset($banners[$m[1]]);
                banner_manager_save_settings();
                unset($_POST);
                break;
            }
        }
    }
    if (isset($_POST["type"])) $edit_type = $_POST["type"];

    // We are editing a banner.
    if ($do_edit != NULL && isset($banners[$do_edit])) {
        $banner = $banners[$do_edit];
        include("settings_banners_edit.php");
        return;
    }

    // We add a banner.
    if (isset($_POST["add"]))
    {
        $name = trim($_POST["new_name"]);
        $id = base64_encode($name);

        if ($name == '') {
          show_error("The block name was empty");
        } elseif (isset($banners[$id])) {
          show_error("The block name is already in use by another block");
        } else {
          $banner = array (
              "id"        => $id,
              "name"      => $name,
              "block"     => $block,
              "timestamp" => time()
          );
          $banners[$id] = $banner;
          $edit_type = "add";
          banner_manager_save_settings();
          include("settings_banners_edit.php");
          return;
        }
    }
?>
    On this page, you can define the banner blocks that you want to
    use on the Phorum pages. Each banner block consists of HTML
    and/or PHP code that has to be inserted in the page. After defining
    your banner blocks, you can use "Link banner blocks to pages" to
    link these to arbitrary Phorum pages.

    <br/><br/>

    <form style="display:inline" action="admin.php" method="post">
    <input type="hidden" name="module" value="modsettings"/>
    <input type="hidden" name="mod" value="banner_manager"/>
    <input type="hidden" name="panel" value="banners"/>
    <input type="hidden" name="phorum_admin_token"
               value="<?php print $PHORUM['admin_token'] ?>"/>

    <table border="0" cellspacing="2" cellpadding="2" class="input-form-table" width="100%">
    <tr class="input-form-tr">
      <td colspan="2" class="input-form-td-break">
          Banner blocks
      </td>
    </tr>
    <tr class="input-form-tr">
      <td class="input-form-th">
        <input type="text" name="new_name" value="<?php print htmlspecialchars($_POST["new_name"]) ?>" size="55" maxlength="55"/>
      </td>
      <td class="input-form-td">
        <input type="submit" name="add" value="Add block"/>
      </td>
    </tr>

<?php foreach (array_reverse($banners) as $id => $data) { ?>
    <tr class="input-form-tr">
      <td class="input-form-th"><?php print htmlspecialchars($data["name"])?></td>
      <td class="input-form-td">
        <input type="submit" name="edit:<?php print htmlspecialchars($id)?>" value="Edit"/>
        <input type="submit" name="delete:<?php print htmlspecialchars($id)?>" value="Delete" onClick="return confirm('Are you sure?')"/>
      </td>
    </tr>
<?php } ?>

    </form>

