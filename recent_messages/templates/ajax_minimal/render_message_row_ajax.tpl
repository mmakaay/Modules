{IF SHOW_FORUM}{VAR DISPLAY_FORUM 0}{/IF}

<?php $classes = array(); ?>

{IF MESSAGE->sort PHORUM_SORT_STICKY}
  <?php $classes[] = 'prm-sticky'; ?>
{/IF}

{IF MESSAGE->new}
  <?php $classes[] = 'prm-new'; ?>
{/IF}

<?php if ($classes) $PHORUM['DATA']['subjectclass'] = implode(' ', $classes); ?>

<div class="prm-row">

  <div class="prm-subject">
    <a {IF subjectclass}class="{subjectclass}"{/IF}
       href="{MESSAGE->URL->READ}">{MESSAGE->subject}</a>
  </div>

  {IF DISPLAY_FORUM}
  <div class="prm-forum">
    {LANG->Forum}:
    <a href="{MESSAGE->URL->FORUM}">{MESSAGE->forum_name}</a>
  </div>
  {/IF}

  <div class="prm-author">
  {IF VIEW_TYPE 2 OR VIEW_TYPE 1}{LANG->StartedBy}{ELSE}{LANG->Author}{/IF}:
  {IF MESSAGE->URL->PROFILE}<a href="{MESSAGE->URL->PROFILE}">{/IF}
  {MESSAGE->author}
  {IF MESSAGE->URL->PROFILE}</a>{/IF}
  </div>

</div>
