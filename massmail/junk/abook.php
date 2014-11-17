<?php

require_once('lib/vcard_class.php');

$basedir='/var/webdav-lenin/test/';

error_reporting(E_ERROR | E_PARSE);


echo "<html>\n<body style='background:rgb(200,200,200);'>\n";


  if( ((isset($_GET['file']))or( isset($_SESSION['file']))) and (!isset($_GET['top'])) and (!isset($_GET['remove'])) ){
    if(isset($_GET['file'])){$_SESSION['file']=$_GET['file'];}

//********************************addressbuch***********************************************
//******************************************************************************************
$karte= new Vcard();
if(isset($_GET['file'])){
  $file=str_replace("'","",$_GET['file']);
  if($file=="newfile"){
    $file = $basedir.md5(uniqid()).".vcf";
    system("touch ".$file);
    //echo $file;
    $karte->setFileName($file);
    $_SESSION['file']=$file;
    $karte->setVersion("3.0");
    $karte->setName("name","kein","","","");
    $karte->setFormattedName();
    $karte->setList(true);
    $karte->toFile(); 
  }else{
    $_SESSION['file']=$file;
  }
}

$file=$_SESSION['file'];
if(""==$file){$file=$dfile;}

$karte->setFileName($file);
$karte->fromFile(true);
$karte->save_html();
$karte->toFile();

$karte->setExportDir("/var/www/massmail/export/");
$karte->exportCSV();
$karte->exportVCF();
$karte->exportPDF();

echo "Zum <a href='".$_SERVER['PHP_SELF']."?top=yes'>Adressbuch</a> gehen. \n";
echo "Diese Liste als 
   <a href='export/".$karte->getValue('FN').".pdf'>PDF</a>,&nbsp;
   <a href='export/".$karte->getValue('FN').".csv'>CSV</a>,&nbsp;
   <a href='export/".$karte->getValue('FN').".vcf'>VCF</a>
 exportieren.";

if(isset($_SESSION['file'])){
  $t="?file=".basename($_SESSION['file']);
}else{
  $t="";
}
 echo " An diese Liste eine <a href='index.php".$t."'> Mail </a> schicken.<br><br>\n";

$karte->edit_html();

//********************************addressbuch**********************************************
//******************************************************************************************

}else{

//********************************liste***********************************************
//******************************************************************************************




$abook= new AddressBook();
$abook->setBook($basedir);
$abook->setName('vpod-global');
$abook->orderByFN();
$abook->saveHtml();


echo "<html><body style='background:rgb(200,200,200);'>";
echo "Zum <a href='index.php'>Mailprogramm</a> gehen.";
echo "<h2>Adressbuch: ".$abook->name."</h2>\n";

$abook->editHtml();


}
    
echo "</body></html>";

?>
