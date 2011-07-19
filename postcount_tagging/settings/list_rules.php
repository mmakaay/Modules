<?php if (!defined('PHORUM_ADMIN')) return; ?>

<div class="PhorumAdminTitle">
  List of configured rules
</div>

<table border="0" cellspacing="2" cellpadding="3" width="100%">
<tr>
  <td class="PhorumAdminTableHead">Name</td>
  <td class="PhorumAdminTableHead">Actions</td>
</tr>

<?php
$rules = postcount_tagging_get_rules();

if (empty($rules))
{ ?>
    <tr>
      <td colspan="2" class="PhorumAdminTableRow">
        <i>There are no rules configured</i>
      </td>
    </tr> <?php
}
else foreach ($rules as $rule)
{
    $edit_url   = $base_url . '&edit_rule=1&id='   . $rule['id'];
    $copy_url   = $base_url . '&copy_rule=1&id='   . $rule['id'];
    $delete_url = $base_url . '&delete_rule=1&id=' . $rule['id'];
    ?>
    <tr>
      <td class="PhorumAdminTableRow">
        <a href="<?php print $edit_url ?>">
          <?php print htmlspecialchars($rule['name']) ?>
        </a>
      </td>
      <td class="PhorumAdminTableRow">
        <a href="<?php print $edit_url   ?>">Edit</a> |
        <a href="<?php print $copy_url   ?>">Copy</a> |
        <a href="<?php print $delete_url ?>">Delete</a>
      </td>
    </tr> <?php
}
?>

<tr>
  <td class="PhorumAdminTableRow">
    &nbsp;
  </td>
  <td class="PhorumAdminTableRow">
    <a href="<?php print $base_url?>&edit_rule=1">Add a new rule</a>
  </td>
</tr>

</table>

