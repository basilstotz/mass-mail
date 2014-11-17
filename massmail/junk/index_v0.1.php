<?php
session_start();
if(!isset($subject)){$subject="";}
if(!isset($text)){$text="";}
//if(!isset($attach)){$attach="";}
if(!isset($liste)){$liste="nolist";}
if(!isset($user)){$user="nobody";}
if(!isset($name)){$name="noname";}

include('../ckeditor/ckeditor.php');
include('../ckfinder/ckfinder.php');
?>




<html>


<body style="background:rgb(200,200,200);">
<h1>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Massen Mail</h1>

<?php

//echo session_name()."<br>";
//echo session_id()."<br>";

$basedir=dirname($_SERVER['SCRIPT_FILENAME']);
//echo $basedir."<br>\n";

?>

<?php
$dir = "/var/webdav-lenin/kontakte/";
$j=0;
// Öffnen eines bekannten Verzeichnisses und danach seinen Inhalt einlesen
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            //echo "filename: $file\n<br>";

            $inhalt=file_get_contents($dir.$file);

            $inhalt=str_replace("\r\n ","",$inhalt);
            $inhalt=str_replace("\r\n","\n",$inhalt);
            $array=split("\n",$inhalt);

//print_r( $array);

            $is_list=1;
            $num=count($array);
            for($i=0;$i<$num;$i++){
                  if($array[$i]=="X-EVOLUTION-LIST:TRUE"){$is_list=0;}
            }
            if($is_list==0){
               //print_r($array);
               //echo $num;
               for($i=0;$i<$num;$i++){
                  //echo "g";
                  $line=split(":",$array[$i]);
                  $tags=split(";",$line[0]);
                   //echo $tags[0]."--->".$line[1]."\n";
                  if($tags[0]=="FN"){
                      $vcards['name'][$j]=$line[1];
	              $vcards['file'][$j]=$file;
                      $vcards_name[$file]=$line[1];
                      $j++;
                  }
               }
             }

        }
        closedir($dh);
        $zahl=$j;
    }
}
array_multisort($vcards['name'],$vcards['file']);
?>


<?php


if(isset($_POST['upload'])and($_POST["upload"]=="Laden")){
}

$ok=false;
if(isset($_POST["user"])){
  $ok=true;

  if(isset($_POST['attached'])){$attach=$_POST['attached'];}

  if(isset($_FILES['attach'])){
    if(!file_exists($basedir.'/attach/'.session_id())){
      if(!mkdir($basedir.'/attach/'.session_id())){echo "mkdir<br>";}
    }
    if($_FILES['attach']['error']==0){
       move_uploaded_file($_FILES['attach']['tmp_name'],$basedir.'/attach/'.session_id()."/".$_FILES['attach']['name']);
    }
  }  


  if($_POST["user"]=="nobody"){
      if($ok){echo "Fehler: ";}
      $user="nobody";
      $ok=false; 
      echo "Absender ";
  }else{
      $user=$_POST["user"];
  }

  if($_POST["subject"]==""){
      if($ok){echo "Fehler: ";}
      $subject="";
      $ok=false;
      echo "Betreff ";
  }else{
      $subject=$_POST["subject"];

  }

  if($_POST["text"]==""){
      if($ok){echo "Fehler: ";}
      $text="";
      $ok=false;
      echo "Text ";
  } else{
      $text=$_POST["text"];
  }

  if($_POST['liste']=="nolist"){
      if($ok){echo "Fehler: ";}
      $liste="nolist";
      $name="noname";
      $ok=false;
      echo "Empf&auml;nger ";
  }else{
      $liste=$_POST['liste'];
      $name=$vcards_name[$liste];

      //liste in tabelle mail
      $inhalt=file_get_contents('/var/webdav-lenin/kontakte/'.$liste);
      $inhalt=str_replace("\r\n ","",$inhalt);
      $inhalt=str_replace("\r\n","\n",$inhalt);
      $array=split("\n",$inhalt);
      $num_mails=0;
      $fh=fopen($basedir.'/liste.txt','w');
      for($i=0;$i<count($array);$i++){
         $line=split(":",$array[$i]);
         $tags=split(";",$line[0]);
                //echo $tags[0]."--->".$line[1]."\n";
         if($tags[0]=="EMAIL"){
             $mails[$num_mails++]=$line[1];
             fprintf($fh,"%s\n",$line[1]);
         }
      }
      fclose($fh);
  }

}else{$ok=false;}

//if($ok){echo "Status: ok<br>\n";}

if(isset($_POST['reset'])){
  $attach="";
  $text="";
  $subject="";
  $attached="";
  $liste="nolist";
  $user="nobody";
  $ok=false;
  $name="noname";
  $dir = $basedir."/attach/".session_id()."/";

    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if($file[0]<>"."){unlink($dir.$file);}
        }
        closedir($dh);
    }
 rmdir($dir);
}


if(($ok)and(isset($_POST['senden']))and($_POST["senden"]=="Senden")){
  echo "Senden....";
  // mailing is done here!!!!!!!!!!!!!!!!!!!!!!!!

  //verbinden
  $link = mysql_connect('localhost', 'root', 'AmEsadS');
  if (!$link) {
    die( 'keine Verbindung möglich: ' . mysql_error());
   }
  mysql_select_db("massmail");

  //job in tabelle job
  $sql="select user_id from user where name='$user'";
  $result=mysql_query($sql);
  $row = mysql_fetch_array($result, MYSQL_ASSOC);
  $user_id=$row['user_id'];
  $sql="insert into job set datum=now(),user='$user_id',subject='$subject',text='$text', liste='$name'";
  $result=mysql_query($sql);
  $job_id=mysql_insert_id();
  
  //liste in tabelle mail
  for($j=0;$j<$num_mails;$j++){
      $the=$mails[$j];
      $sql="insert into mail set job='$job_id',mail='$the',user='$user_id'";
      $result=mysql_query($sql);
      if(mysql_errno()<>0){echo mysql_errno() . ": " . mysql_error() . "<br>\n";}
  }

  
  //atachments
  $num_attachments=0;
  $dir = $basedir."/db/";
  if(!file_exists($dir.$job_id)){
    mkdir($dir.$job_id);
  }
  if(file_exists( $basedir.'/attach/'.session_id())){
    if ($dh = opendir( $basedir.'/attach/'.session_id())) {
      while (($file = readdir($dh)) !== false) {
        if($file[0]<>"."){
          $num_attachments++;
          copy($basedir.'/attach/'.session_id().'/'.$file,$basedir.'/db/'.$job_id.'/'.$file);
        }
      }
      closedir($dh);
    }
  }

  //job aktivieren
  $sql="update job set mails='".$num_mails."',attachments='".$num_attachments."',status='waiting' where job_id='".$job_id."'";
  $result=mysql_query($sql);
  if(mysql_errno()<>0){echo mysql_errno() . ": " . mysql_error() . "<br>\n";}
}





//echo "<br><br>";
?>

<form action="<?php $_SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data">

<?php
/*
 <input name="vcard" type="hidden" value="<?php echo $vcard; ?>">
*/
?>

<table width='100%'>
<tr><td align="right">Absender:</td><td> 
   <select name="user" size="1">
      <option <?php if($user=="nobody"){echo "selected";} ?> value="nobody"></option>
      <option <?php if($user=="manager"){echo "selected";} ?> value="manager">Basil Stotz</option>
      <option <?php if($user=="edith"){echo "selected";} ?> value="edith">Edith J&auml;cke</option>
      <option <?php if($user=="heidi"){echo "selected";} ?> value="heidi">Heidi M&uuml;ck</option>
      <option <?php if($user=="susanne"){echo "selected";} ?> value="susanne">Susanne Nese</option>
      <option <?php if($user=="matthias"){echo "selected";} ?> value="matthias">Matthias Scheurer</option>
      <option <?php if($user=="franziska"){echo "selected";} ?> value="franziska">Franziska Sager</option>
      <option <?php if($user=="marianne"){echo "selected";} ?> value="marianne">Marianne Meyer</option>
    </select>
</td>
</tr><tr><td align="right">
Empf&auml;nger:</td><td>

<?php

echo "<select name='liste' size='1'>\n";
echo "<option value='nolist'></option>\n";
for($i=0;$i<$zahl;$i++){
  if($vcards['file'][$i]==$liste){$sel=" selected ";}else{$sel=" ";}
  echo "<option value='".$vcards['file'][$i]."' ".$sel." >".$vcards['name'][$i]."</option>\n";
}
echo "</select>\n";
?>

<input name="refresh" type="submit" value="Laden">

</td></tr><tr>
<tr><td>&nbsp;</td><td>
<?php
if($name<>"noname"){
  //echo "<a href='http://lenin.local/webdav/kontakte/".$liste."' target='_blanc'>".$name.".vcf</a>\n";
  echo "<a href='liste.txt' target='_blanc'>".$name.".txt</a>\n";
}
?>
</td></tr>
<td align="right">Betreff:</td><td><input name="subject" value="<?php echo $subject; ?>" type="text" size="61" maxlength="30">
</td></tr>
<tr valign="top">
<td align="right">Text:</td><td>
<!---
<textarea name="text" cols="70" rows="15" wrap="hard"><?php echo $text; ?></textarea> 
---!>
<?php
$cke= new CKeditor();
$config=array();
$config['toolbar']= array(
  array('Source','-','Bold', 'Italic', 'Underline', 'Strike','-', 'Subscript','Superscript','-','NumberedList','BulletedList','-','Outdent','Indent','Blockquote'),
  array('JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','Font','FontSize','TextColor'),
  array('Link','Unlink','Image','Table','HorizontalRule')
);
$config['fullPage']=true;

CKFinder::SetupCKEditor($cke);

$cke->editor("text",$text,$config);
?>
</td></tr>

<tr valign="top"><td align="right">
  Anhang:</td><td>

<?php
  $dir = "/var/www/massmail/attach/";
  if(file_exists($dir.session_id())){
    if (is_dir($dir.session_id())) {
      if ($dh = opendir($dir.session_id())) {
        while (($file = readdir($dh)) !== false) {
            if($file[0]<>"."){echo $file."<br>\n";}
        }
        closedir($dh);
      }
    }
  }

     echo "<input name='attach' type='file' size='61'>";
     echo "<input name='upload' type='submit' value='Laden'>";

?>

</td></tr>
<tr><td>&nbsp;</td><td></td></tr>
<tr><td align="right">

</td><td>
        <input name="attached" type="hidden" value='<?php echo $attach; ?>'>
        <input name="senden" type="submit" value="Senden">
        <input name="reset" type="submit" value="Abbrechen">
</td></tr>
</table>
</form>

</body>
</html>
