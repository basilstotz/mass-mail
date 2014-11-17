<?php
require_once('File/IMC.php');
require_once('lib/vcard_class.php');

$basedir='/var/webdav-lenin/kontakte/';

//$file='7EF2F178-425565C6-261CB5E7.vcf';
error_reporting(E_ERROR | E_PARSE);


$abook= new AddressBook($basedir,'vpod-global');

echo "<html><body style='background:rgb(200,200,200);'>";
echo "<h2>Adressbuch: ".$abook->name."</h2>\n";


$abook->readBook();
$abook->saveHtml();
$abook->viewHtml();

echo "</body></html>";

?>
