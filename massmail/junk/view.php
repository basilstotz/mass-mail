<?php
session_start();

require_once('File/IMC.php');
include('lib/read_lists.php');
include('lib/fetch.php');

$dir = "/var/webdav-lenin/kontakte/";

$vcards=read_lists($dir);

if(isset($_GET['liste'])){
 $liste=$_GET['liste'];
 $_SESSION['liste']=$liste;
}else{
   if(isset($_SESSION['liste'])){
     $liste=$_SESSION['liste'];
   }else{
     $liste="nolist";
   }  
}

//if(isset($_SESSION['liste'])){$liste=s_SESSION['liste'];
if(!isset($liste)){$liste="nolist";}
if($liste=="nolist"){$titel="VCards....";}else{$titel=$liste;}


?>

<html>
<body style='background:rgb(200,200,200)'>


<h1><?php echo $titel; ?></h1>

<?php
echo "<a href='index.php?PHPSESSID=".session_id()."'>zur&uuml;ck</a>";
?>

<form action="<?php echo $_SERVER['PHP_SELF'];?>">


Liste:
<?php
echo "<select name='liste' size='1'>\n";
echo "<option value='nolist'></option>\n";
for($i=0;$i<count($vcards['name']);$i++){
  if($vcards['file'][$i]==$liste){$sel=" selected ";}else{$sel=" ";}
  echo "<option value='".$vcards['file'][$i]."' ".$sel." >".$vcards['name'][$i]."</option>\n";
}
echo "</select>\n";
?>


<input name="senden" type="submit" value="Aktualisieren">

<!----
<input name="fullname" type="hidden" value="<?php echo $fullname; ?>">
</form>

----!>


<?php

if($liste<>"nolist"){
echo "<a href='edit.php?liste=".$liste."'>diese Liste bearbeiten</a>";
echo "<a href='edit.php?liste=nolist'>neue Liste erstellen</a>";
echo "<br>\n";

 
echo "<a href='liste.csv'>liste.csv</a>\n";
echo "<a href='liste.tex'>liste.tex</a>\n";
echo "<a href='liste.vcf'>liste.vcf</a>\n";
echo "<a href='liste.pdf'>liste.pdf</a>\n";

// create vCard parser
$parse = File_IMC::parse('vCard');
$cardinfo = $parse->fromFile($dir.$liste);
echo fetch($cardinfo,'html');



file_put_contents('/var/www/massmail/liste.csv',fetch($cardinfo,'csv'));
file_put_contents('/var/www/massmail/liste.vcf',fetch($cardinfo,'vcf'));
file_put_contents('/var/www/massmail/liste.tex',fetch($cardinfo,'tex'));
system("latex /var/www/massmail/liste.tex >/dev/null 2>/dev/null");
system("dvipdf /var/www/massmail/liste.dvi");


}
?>

</form>

</body>
</html>