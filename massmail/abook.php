<?php

require_once('lib/vcard_class.php');

require_once('config.php');

error_reporting(E_ERROR | E_PARSE);



$abook= new AddressBook();
$abook->setBook($bookdir);
$abook->orderByFN();
$abook->saveHtml();



echo "<html>\n<body style='background:rgb(200,200,200);'>\n";


echo "<html><body style='background:rgb(200,200,200);'>";
echo "Zum <a href='index.php'>Mailprogramm</a> gehen.<br><br>";


$abook->editHtml();

    
echo "</body></html>";

?>
