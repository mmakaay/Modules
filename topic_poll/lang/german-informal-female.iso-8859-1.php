<?php

// This file ist part of the German Language Files Package
// Get the complete package here:
// http://www.phorum.org/phorum5/read.php?65,129717

// Diese Datei ist Teil des Deutschen Sprachpakets
// Das komplette Paket gibt es hier:
// http://www.phorum.org/phorum5/read.php?65,129717

include(str_replace('-informal-female', '-informal-male', __FILE__));

$PHORUM['DATA']['LANG']['mod_topic_poll']['DenyAnonymous']       = 'Nur registrierte Teilnehmerinnen dürfen für diese Umfrage abstimmen';
$PHORUM['DATA']['LANG']['mod_topic_poll']['NoEditAfterVotes']    = 'Du kannst diese Umfrage nicht mehr bearbeiten, da es bereits Antworten gibt.<br/>Nur Moderatorinnen können die Umfrage jetzt noch ändern.';
$PHORUM['DATA']['LANG']['mod_topic_poll']['PermissionAnonymous'] = 'Alle Teilnehmerinnen und Gäste können abstimmen';
$PHORUM['DATA']['LANG']['mod_topic_poll']['PermissionUser']      = 'Nur registrierte Teilnehmerinnen können abstimmen';
$PHORUM['DATA']['LANG']['mod_topic_poll']['VotingOpenForAll']    = 'Alle Teilnehmerinnen und Gäste können abstimmen';
$PHORUM['DATA']['LANG']['mod_topic_poll']['VotingUsersOnly']     = 'Nur für registrierte Teilnehmerinnen';

?>
