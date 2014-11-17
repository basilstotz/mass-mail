
<?php

require_once('File/IMC.php');
include('lib/read_lists.php');
include('lib/fetch.php');

$dir = "/var/webdav-lenin/kontakte/";

$vcards=read_lists($dir);

if(isset($_GET['liste'])){
 $liste=$_GET['liste'];
}

if(!isset($liste)){$liste="nolist";}
if($liste=="nolist"){$titel="VCards....";}else{$titel=$liste;}


?>

<html>
<body style='background:rgb(200,200,200)'>

<h1><?php echo $titel; ?></h1>

<form action="<?php $_SERVER['PHP_SELF']?>">



<input name="senden" type="submit" value="Aktualisieren">
<input name="edieren" type="submit" value="Edieren">


<?php

if($liste<>"nolist"){
  // create vCard parser
  $parse = File_IMC::parse('vCard');
  $cardinfo = $parse->fromFile($dir.$liste);

  put($cardinfo);

  echo fetch($cardinfo,'edit');

}
?>

</form>

</body>
</html>