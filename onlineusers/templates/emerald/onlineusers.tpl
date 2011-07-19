<div class="generic mod_onlineusers">

  <h1>{LANG->mod_onlineusers->OnlineUsers}</h1>

  {IF MOD_ONLINEUSERS->USERS}
    <div class="onlineusers_users">
      {VAR FIRST 1}
      {LOOP MOD_ONLINEUSERS->USERS}{VAR OU MOD_ONLINEUSERS->USERS}{IF NOT FIRST}, {/IF}<a href="{OU->PROFILE}">{OU->NAME}</a> {IF OU->ADMIN OR OU->IDLE}({/IF}{IF OU->ADMIN}{LANG->mod_onlineusers->Administrator}{/IF}{IF OU->ADMIN AND OU->IDLE}, {/IF}{IF OU->IDLE}{OU->IDLE}{/IF}{IF OU->ADMIN OR OU->IDLE}){/IF}{VAR FIRST 0}{/LOOP MOD_ONLINEUSERS->USERS}
    </div>
  {/IF}

  {IF MOD_ONLINEUSERS->SHOW_GUESTS}
    <div class="onlineusers_guests">
      {LANG->mod_onlineusers->Guests}:
      {MOD_ONLINEUSERS->GUESTCOUNT}
    </div>
  {/IF}

  {IF MOD_ONLINEUSERS->RECORD_USERCOUNT OR MOD_ONLINEUSERS->RECORD_GUESTCOUNT}
  <div class="onlineusers_records">

    {IF MOD_ONLINEUSERS->RECORD_USERCOUNT}
      <div class="onlineusers_record">
        {LANG->mod_onlineusers->RecordNumberOfUsers}:
        {MOD_ONLINEUSERS->RECORD_USERCOUNT}
        {LANG->mod_onlineusers->on} {MOD_ONLINEUSERS->RECORD_USERCOUNT_DATE}
      </div>
    {/IF}

    {IF MOD_ONLINEUSERS->RECORD_GUESTCOUNT}
      <div class="onlineusers_record">
        {LANG->mod_onlineusers->RecordNumberOfGuests}:
        {MOD_ONLINEUSERS->RECORD_GUESTCOUNT}
        {LANG->mod_onlineusers->on} {MOD_ONLINEUSERS->RECORD_GUESTCOUNT_DATE}
      </div>
    {/IF}

  </div>
  {/IF}

</div> 
