<tr class="footer">
  <th colspan="4">
    {IF URL->PREV_PAGE}
      <a href="{URL->PREV_PAGE}">{LANG->mod_recent_messages->NewerMessages}</a>
    {/IF}
    {IF URL->PREV_PAGE AND URL->NEXT_PAGE}
      &nbsp;&bull;&nbsp;
    {/IF}
    {IF URL->NEXT_PAGE}
      <a href="{URL->NEXT_PAGE}">{LANG->mod_recent_messages->OlderMessages}</a>
    {/IF}
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {LANG->Page} {CURRENT_PAGE}
  </th>
</tr>

</table>

<?php $lang = $PHORUM['DATA']['LANG']['mod_recent_messages']; ?>

<script type="text/javascript">
//<![CDATA[

  var $footer = $PJ('.recent_messages tr.footer');
  $footer.hide();

  var $loadmore = $PJ(
    '<tr class="loadmore">' +
      '<th colspan="4" style="height:20px">' +
        '<a href="#" onclick="return loadMoreRecentMessages()"' +
        ' style="text-align:center; display:block" class="loadmore_link">' +
        '<?php print addslashes($lang['OlderMessages']); ?>' +
        '</a>' +
        '<div class="loadmore_busy"' +
        ' style="text-align:center; display:none; height:20px">' +
        '...' +
        '</div>' +
      '</th>' +
    '</tr>'
  );

  $loadmore.phorum = {
    page: {CURRENT_PAGE},
    busy: false
  };

  $loadmore.insertBefore($footer);

  var $tmptable = $PJ('<table style="display:none"></table>');
  $PJ('#phorum').append($tmptable);

  function loadMoreRecentMessages()
  {
    if ($loadmore.busy) return;

    $loadmore.phorum.page ++;
    $loadmore.phorum.busy = true;
    $loadmore.find('.loadmore_busy').show();
    $loadmore.find('.loadmore_link').hide();

    Phorum.Ajax.call({
        call         : 'mod_recent_messages',
        show_amount  : {SHOW_AMOUNT},
        page         : $loadmore.phorum.page,
        content_only : 1, 

        onSuccess: function (result)
        {
            // Store the result rows in our hidden temporary table.
            $tmptable.html(result);

            // Transfer all result rows to the final recent messages table.
            var count = 0;
            $tmptable.find('tr').each(function ()
            {
              $row = $PJ(this);
              count ++;

              // If the first row is a grouping header, then check if this
              // grouping header hasn't already been put on screen for a
              // previous page. If yes, then we skip this row.
              if (count == 1)
              {
                window.r = $row;
                $newgroup  = $row.find('td.group:first');
                $lastgroup = $loadmore.closest('table').find('td.group:last');
                if ($newgroup.length &&
                    $lastgroup.length &&
                    $newgroup.html() == $lastgroup.html()) {
                  return;
                }
              }

              // Move the row to the final table and make it visible
              // using a nice fade-in effect.
              $row.hide();
              $row.insertBefore($loadmore);
              $row.fadeIn();
            });

            $loadmore.phorum.busy = false;

            if (count) {
                $loadmore.find('.loadmore_link').show();
                $loadmore.find('.loadmore_busy').hide();
            } else {
                $loadmore.find('.loadmore_busy').html('<?php
                  print addslashes($PHORUM['DATA']['LANG']['NoResults']);
                ?>');
            }
        },

        onFailure: function (result)
        {
            $loadmore.phorum.busy = false;
            $loadmore.find('.loadmore_link').show();
            $loadmore.find('.loadmore_busy').hide();
        }
    }); 

    return false;
  }

//]]>
</script>
