<?php

require_once('lib/vcard_class.php');

require_once('config.php');

error_reporting(E_ERROR | E_PARSE);


$agent= new Mua($db_options,$mail_options);
$agent->setUsers($users);      //from config.php
$agent->setBook($bookdir);     //      "
$agent->orderByFN();
$agent->saveHtml();

?>

<html>

<body style="background:rgb(200,200,200);">
Zum <a href='abook.php'>Adressbuch</a> wechseln.
<h2><img src='logo-gmail.png' alt=''>Massen Mail</h2>

<?php $agent->editHtml(); ?>

</body>
</html>
