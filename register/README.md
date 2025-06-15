# SvxReg
<h1> php based editor for a master and back up reflector, permitting the additions of new users to an existing svxreflector with generated passwords and commitment of the new entry by restarting the reflectors.</h1>

<p>We need only address the master svxreflector, if we have a back-up svxreflector that we can hot-save to. In fact we need only send the svxreflector.conf file to the back-up svxreflector and restart it, as the database already in place is identical to the master, and we can do this at any time.</p>

<p>This code only requires the feed point of the mailer.php to be added to the process_registration.php file where indicated - line 154 of process_registration.php.</p>

<h2>Caution - This code is live - and will change the svxreflector.conf file.</h2>
<p>Use the one provided in portal.svxlink.uk:48300 to test it.</p>