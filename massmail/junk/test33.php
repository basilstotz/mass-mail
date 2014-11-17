<?php
require_once('File/IMC.php');
require_once('lib/vcard_class.php');

$basedir='/var/webdav-lenin/kontakte/';
$dfile='7EF2F178-425565C6-261CB5E7.vcf';
error_reporting(E_ERROR | E_PARSE);

echo "<html><body style='background:rgb(200,200,200);'>\n";

echo "<a href='test2.php'>zur&uuml;ck zum Adressbuch</a><br><br>\n";
echo "Diese Liste als <a href='uhu.def'>PDF</a>,&nbsp;<a href='uhu.def'>CSV</a>,&nbsp;<a href='uhu.def'>VCF</a> exportieren.";
echo " An diese Liste eine <a href='index.php'> Mail </a> schicken.<br>\n";
$karte= new Vcard();
if(isset($_GET['file'])){
  $file=str_replace("'","",$_GET['file']);
  if($file=="newfile"){
    $file = $basedir.md5(uniqid()).".vcf";
    system("touch ".$file);
    echo $file;
    $karte->setFileName($file);
    $_SESSION['file']=$file;
    $karte->setVersion("3.0");
    $karte->setName("name","kein","","","");
    $karte->setFormattedName();
    $karte->setList(true);
    $karte->toFile(); 
  }else{
    //echo $file."<br>";;
    //$karte->setFileName($file);
    $_SESSION['file']=$file;
  }
}

$file=$_SESSION['file'];
if(""==$file){$file=$dfile;}
$karte->setFileName($file);
$karte->fromFile();
$karte->save_html();
//$karte->toFile();
$karte->edit_html();

    
echo "</body></html>";

?>
