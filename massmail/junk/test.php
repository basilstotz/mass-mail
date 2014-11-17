<?php
require_once 'File/IMC.php';

// create vCard parser
$parse = File_IMC::parse('vCard');

// parse a vCard file and store the data in $cardinfo
$cardinfo = $parse->fromFile('/var/webdav-lenin/kontakte/7EF2F178-425565C6-261CB5E7.vcf');

// view the card info array
echo '<pre>';
//print_r($cardinfo);
echo '</pre>';


// instantiate a builder object
// (defaults to version 3.0)
$vcard = File_IMC::build('vCard');

$vcard->setFromArray($cardinfo['VCARD']);

// set a formatted name
//$vcard->setFormattedName('Bolivar Shagnasty');

// set the structured name parts
//$vcard->setName('Shagnasty', 'Bolivar', 'Odysseus', 'Mr.', 'III');

// add a work email.  note that we add the value
// first and the param after -- Contact_Vcard_Build
// is smart enough to add the param in the correct
// place.
//$vcard->addEmail('boshag@example.com');
//$vcard->addParam('TYPE', 'WORK');

// add a home/preferred email
$vcard->addEmail('bolivar@example.net');
$vcard->addParam('TYPE', 'HOME');
$vcard->addParam('TYPE', 'PREF');

// add a work address
//$vcard->addAddress('POB 101', 'Suite 202', '123 Main',
//                   'Beverly Hills', 'CA', '90210', 'US');
//$vcard->addParam('TYPE', 'WORK');



echo '<pre>';
print_r($vcard);
echo '</pre>';


// get back the vCard and print it
$text = $vcard->fetch();
echo '<pre>';
print_r($text);
echo '</pre>';
    
?>