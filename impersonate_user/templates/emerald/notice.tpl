<div style="display: none;
            position: fixed;
            left: 4px;
            top: 4px;
            z-index: 999;
            text-align: left;
            font-family: arial,helvetica,sans-serif;
            border: 2px solid darkorange;
            padding: 8px;
            font-size: 70%;
            margin-bottom: 20px;
            background-color: #f5f5f5" id="mod_impersonate_user_div">
      {MOD_IMPERSONATE_USER->NOTICE}<br/>
      <img src="{MOD_IMPERSONATE_USER->URL->TEMPLATES}/key_go.png"/>
      <a href="{MOD_IMPERSONATE_USER->URL->SWITCHBACK}">
          {MOD_IMPERSONATE_USER->SWITCHBACK}
      </a><br/>
      <img src="{MOD_IMPERSONATE_USER->URL->TEMPLATES}/delete.png"/>
      <a href="javascript:mod_impersonate_user_drop_notice()">
          {LANG->mod_impersonate_user->DropNotice}
      </a><br/>
</div>

<script type="text/javascript">
// <![CDATA[

// Move the impersonate user notification div to the top of the page.
d = document.getElementById('mod_impersonate_user_div');
b = document.body;
if (d && b) {
  b.insertBefore(d, b.childNodes[0]);
  d.style.display = 'block';
}

function mod_impersonate_user_drop_notice()
{
  // Hide the notification div.
  d = document.getElementById('mod_impersonate_user_div');
  if (d) d.style.display = 'none';

  // Clear the switchback cookie.
  document.cookie = '{MOD_IMPERSONATE_USER->CLEAR_COOKIE}';
}

// ]]>
</script>

