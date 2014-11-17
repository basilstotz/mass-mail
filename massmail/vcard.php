<?php

require_once('lib/vcard_class.php');

require_once('config.php');

error_reporting(E_ERROR | E_PARSE);


$karte= new Vcard($bookdir);
$karte->getFileName();
$karte->fromFile();
$karte->save_html();

?>

<html>
<body style='background:rgb(200,200,200);'>

Zum <a href='abook.php'>Adressbuch</a> gehen. Zum <a href='index.php'>Mailprogram</a> gehen.<br><br>


<!--
An diese Liste eine <a href='index.php?file=<?php echo $_SESSION['filename']; ?>'> Mail </a> schicken.<br><br>
----!>

<?php $karte->edit_html(); ?>

    
</body>
</html>


