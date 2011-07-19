<div class="phorum-titleblock">
  <?php print $lang["mod_filelist"]["AllFilesFromThread"] ?>
</div>

<div class="phorum-block">
  <table border="0" cellspacing="0" width="95%"> 
    <tr>
      <th align="left"><?php print $lang["Filename"]?></th>
      <th align="left"><?php print $lang["Filesize"]?></th>
      <th align="left"><?php print $lang["Postedby"]?></th>
      <th align="left"><?php print $lang["Date"]?></th>
      <th align="left">&nbsp;</th>
    </tr>
    <?php
    foreach ($data as $file) {
      print "<tr>";
      print "<td><a href=\"{$file["link_file"]}\">{$file["name"]}</a></td>";
      print "<td>{$file["fmt_size"]}</td>";
      if ($file["link_author"]) {
          print "<td><a href=\"{$file["link_author"]}\">{$file["author"]}</a></td>";
      } else {
          print "<td>{$file["author"]}</td>";
      }
      print "<td>{$file["fmt_datestamp"]}</td>";
      print "<td><a href=\"{$file["link_message"]}\">{$lang["mod_filelist"]["ReadMessage"]}</a></td>";
      print "</tr>";
    }
    ?>
  </table>
</div>

<div class="phorum-endblock"></div>
<br/>

