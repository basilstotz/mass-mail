<?php

require_once('File/IMC.php');
require_once('mailer_class.php');
require_once('../ckeditor/ckeditor.php');
require_once('../ckfinder/ckfinder.php');


class Vcard extends File_IMC_Build_Vcard {

     public $filename;
     public $exportdir;
     public $basedir;
     public $index;

//     public function __construct($version='3.0'){
//        parent::__construct($version);
//     }


     public function Vcard($basedir){
        $this->setExportDir(dirname($_SERVER['SCRIPT_FILENAME'])."/export/");
        $this->basedir=$basedir;
        return File_IMC::build('vCard');
     }


//**************************privat***************************************

     private function split_mail($text){

     $res=array();

             if(false===strpos($text,"<")){
                if(false===strpos($text," ")){     
                   $name="";
                   $mail=$text;
                }else{
                   $mail=$text;
                   $mail=strrchr($mail," ");
                   $name=substr($text,0,strlen($text)-strlen($mail));
                }
             }else{
               $mail=$text;
               $mail=strrchr($mail,"<");
               $name=substr($text,0,strlen($text)-strlen($mail));
             }
             //$mail=str_replace(":","",$mail);
             $mail=str_replace(" ","",$mail);
             $mail=str_replace("<","",$mail);
             $mail=str_replace(">","",$mail);
             $mail=str_replace("'","",$mail);
             $mail=strtolower($mail);

             $name=str_replace("\"","",$name);
             $name=str_replace("'","",$name);

             $res['name']=$name;
             $res['addr']=$mail;

             return $res;
     }


//*********************************************************
     public function setIndex($index){
       $this->index=$index;
     }

     public function setBaseDir($dir){
       $this->basedir=$dir;
     }

     public function setFileName($file){
       $this->filename=$file;
     }

     private function setExportDir($dir){
       if(!file_exists($dir)){system( "mkdir -p ".$dir );}
       $this->exportdir=$dir;
     }

     private function newFile(){
       $file = md5(uniqid()).".vcf";
       $this->filename=$file;
       $this->setVersion("3.0");
       $this->setName("name","kein","","","");
       $this->setFormattedName();
       $this->setList(true);
       $this->toFile();
       return $file;
     }

     public function getFileName(){
       if(isset($_GET['file'])){
          $file=str_replace("'","",$_GET['file']);
          if($file=="newfile"){
             $this->filename=$this->newFile();
          }else{
             $this->filename=$file;
          }
          $_SESSION['filename']=$this->filename;
        }else{
          $this->filename=$_SESSION['filename'];
        }
     }

     public function fromFile(){

       $parser= File_IMC::parse('VCARD');
       $cardinfo=$parser->fromFile($this->basedir.$this->filename);
       $info=$cardinfo['VCARD'];
       $this->setFromArray($info);
       return $this;
     }

     public function toFile(){
       file_put_contents($this->basedir.$this->filename,$this->fetch());
       $this->fromFile(true);
     }


     public function exportVCF(){
        file_put_contents($this->exportdir.$this->getValue('FN').".vcf",$this->fetch());
     }

     public function exportPDF(){
       $enc=mb_internal_encoding();
       mb_internal_encoding("utf-8");
       $res="";
       $res.=sprintf("%s","\\documentclass{scrartcl}\n");
       $res.=sprintf("%s","\\usepackage[utf8]{inputenc}\n");
       //$res.=sprintf("%s","\\pagestyle{empty}\n");
       $res.=sprintf("%s","\\begin{document}\n");
       $res.=sprintf("%s","\\section*{Liste: ".$this->getValue('FN')."}\n");
       //$res.=sprintf("%s","\\begin{tabular}{ll}\n");

       foreach($this->value['EMAIL'] as $key => $val){
           $txt=$this->split_mail(mb_decode_mimeheader($this->getValue('EMAIL',$key)));
           $res.=sprintf("\\textbf{%s} %s\\\\\n",$txt['name'],$txt['addr']);
       }

       //$res.=sprintf("%s","\\end{tabular}\n");
       $res.=sprintf("%s","\\end{document}\n");
      
       file_put_contents($this->exportdir.$this->getValue('FN').".tex",$res);
       system("cd ".$this->exportdir.";pdflatex  \"".$this->getValue('FN').".tex\" >/dev/null 2>/dev/null");
       //system("cd ".$this->exportdir.";pdflatex  \"".$this->getValue('FN').".tex\" ");

       mb_internal_encoding($enc);

     }



     public function exportCSV(){
        $fd=fopen($this->exportdir.$this->getValue('FN').".csv","w");
        fprintf($fd,"\"Name\";\"Email\"\n");
        foreach($this->value['EMAIL'] as $key => $val){
           $l=$this->split_mail(mb_decode_mimeheader($this->getValue('EMAIL',$key)));
           fprintf($fd,"\"%s\";\"%s\"\n",$l['name'],$l['addr']);
        }
        fclose($fd);
     }

     public function exportTXT(){
        $fd=fopen($this->exportdir.$this->getValue('FN').".txt","w");
        foreach($this->value['EMAIL'] as $key => $val){
           $l=$this->split_mail(mb_decode_mimeheader($this->getValue('EMAIL',$key)));
           fprintf($fd,"%s <%s>,\n",trim($l['name']),$l['addr']);
        }
        fclose($fd);
     }

//****************************************************************33

     public function isCorrect(){
        $ans=true;
        foreach($this->value['EMAIL'] as $key => $val){
           $l=$this->split_mail($this->getValue('EMAIL',$key));
           if(false===strpos($l['addr'],"@")){$ans=false;}
        }
        return $ans;
     }



//*******************************************************
     private function setEmail($text,$iter){        
        $this->autoparam = 'EMAIL';
        $this->setValue('EMAIL', $iter, 0, $text);
        return $this;
    }

     public function delEmail($iter){
       unset($this->value['EMAIL'][$iter]);
       unset($this->param['EMAIL'][$iter]);
     }

//***************************************************
     public function getList(){
       $temp=$this->getValue('X-EVOLUTION-LIST');
       if(isset($temp)and($temp=='TRUE')){
         return true;
       }else{
         return false;
       }
     }

     public function setList($schalter){
       if($schalter){
          $this->setValue('X-EVOLUTION-LIST',0,0,'TRUE');
       }else{
          $this->setValue('X-EVOLUTION-LIST',0,0,'FALSE');
       }
     }
        
//**********************************************
     public function addEmailFromText($text){
            $text=str_replace("\n","",$text);
            $lines=split(",",$text);
            for($i=0;$i<count($lines);$i++){
              $l=$this->split_mail($lines[$i]);
              $mail=sprintf("%s <%s>",mb_encode_mimeheader($l['name']),$l['addr']);
              $this->addEmail($mail);
            }
     }

     public function setFamily($text){
      $this->setValue('N',0,FILE_IMC::VCARD_N_FAMILY, $text);
     }

     public function setGiven($text){
      $this->setValue('N',0,FILE_IMC::VCARD_N_GIVEN, $text);
     }

     public function setAddl($text){
      $this->setValue('N',0,FILE_IMC::VCARD_N_ADDL, $text);
     }

     public function setPrefix($text){
      $this->setValue('N',0,FILE_IMC::VCARD_N_PREFIX, $text);
     }

     public function setSuffix($text){
      $this->setValue('N',0,FILE_IMC::VCARD_N_SUFFIX, $text);
     }

     public function getFamily(){
      return $this->getValue('N',0,FILE_IMC::VCARD_N_FAMILY);
     }

     public function getGiven($text){
      return $this->getValue('N',0,FILE_IMC::VCARD_N_GIVEN);
     }

     public function getAddl(){
      return $this->getValue('N',0,FILE_IMC::VCARD_N_ADDL);
     }

     public function getPrefix(){
      return $this->getValue('N',0,FILE_IMC::VCARD_N_PREFIX);
     }

     public function getSuffix(){
      return $this->getValue('N',0,FILE_IMC::VCARD_N_SUFFIX);
     }



//**************************************************************************************3
     public function edit_html(){
       

       $this->exportCSV();
       $this->exportVCF();
       $this->exportPDF();
       $this->exportTXT();

       echo "<div style='background-color:rgb(180,180,180)'>\n";


       echo "Diese Liste als
             <a href='export/".$this->getValue('FN').".pdf'>PDF</a>,&nbsp;
             <a href='export/".$this->getValue('FN').".csv'>CSV</a>,&nbsp;
             <a href='export/".$this->getValue('FN').".vcf'>VCF</a>,&nbsp;
             <a href='export/".$this->getValue('FN').".txt'>TXT</a>,&nbsp;
             exportieren.&nbsp&nbsp;";

       if($this->getValue('BDAY')<>""){
         $bday=str_replace("\\","",$this->getValue('BDAY'));
         echo "Diese Liste wurde zuletzt am " .$bday . " gespeichert<br><br>";
       }

       echo "<form action='".$_SERVER['PHP_SELF']."'>\n";

       echo "<table>\n";
       echo "<tr><td><b>Liste</b></td><td><b>".$this->getValue('FN')."</b></td></tr>\n";
       echo "<tr><td>Gruppe:</td><td><input type='text' name='family' value='".$this->getFamily()."'></td></tr>\n";
       echo "<tr><td>Organisation:</td><td><input type='text' name='given' value='".$this->getGiven()."'></td></tr>\n";
       echo "<tr><td>Abteilung:</td><td><input type='text' name='addl' value='".$this->getAddl()."'></td></tr>\n";
       //echo "<tr><td>Prefix:</td><td><input type='text' name='prefix' value='".$this->getPrefix()."'></td></tr>\n";
       //echo "<tr><td>Suffix:</td><td><input type='text' name='suffix' value='".$this->getSuffix()."'></td></tr>\n";
       
        echo "<tr valign='top'><td>Notiz:</td><td><textarea cols='75' rows='3' name='notiz'>".str_replace("\\","",$this->getValue('NOTE',0,0))."</textarea></td></tr>";

        echo "<tr><td></td><td><b>Listen-Mitglieder</b></td></tr>";

        // email
        // available in both 2.1 and 3.0
        if (is_array($this->value['EMAIL'])) {
            foreach ($this->value['EMAIL'] as $key => $val) {
                $line = mb_decode_mimeheader($this->getValue('EMAIL',$key));
                $line=str_replace("\"","",$line);
                $line=str_replace("\\","",$line);
                $l=$this->split_mail($line);
                echo "<tr><td></td><td><table><tr>";
                echo "<td><input type='text' size='30' name='name' value='".$l['name']."'></td>";
                echo "<td><input type='text' size='30' name='addr' value='".$l['addr']."'></td>";
                echo "<td></td><td><input type='checkbox' name='delete' value='".$key."'><img src='dustbin.gif' height='22px' alt=''></td>";
                echo "</tr></table></td></tr>\n";
            }
        }
        echo "<tr valign='top'><td>Hinzuf&uuml;gen:</td><td><textarea cols='75' rows='3' name='neu'></textarea></td>";
        //echo "<td><input type='submit' name='hinzu' value='Hinzuf&uuml;gen'></td></tr>\n";
        echo "<td></td></tr>\n";

       echo "</table>\n";

       echo "<input type='submit' name='speichern' value='Speichern'>\n";
       echo "</form></div>\n";
     }

     public function save_html(){
   
       //$this->fromFile(); 

       if((isset($_GET['speichern']))or(isset($_GET['hinzu']))){

         $query  = explode('&', $_SERVER['QUERY_STRING']);
         $params = array();
         foreach( $query as $param ){
               list($name, $value) = explode('=', $param);
               $params[urldecode($name)][] = urldecode($value);
         }

         $this->setFamily($params['family'][0]);
         $this->setGiven($params['given'][0]);
         $this->setAddl($params['addl'][0]);
         //$this->setPrefix($params['prefix'][0]);
         //$this->setSuffix($params['suffix'][0]);

       $fn="";
       if($this->getGiven()<>""){$fn.=$this->getGiven();}
       if($this->getAddl()<>""){$fn.=" ".$this->getAddl();}
       if($this->getFamily()<>""){$fn.=" ".$this->getFamily();}
  
       $this->setFormattedName(trim($fn));

         if(isset($params['neu'][0])and($params['neu'][0]<>"")){
            $this->addEmailFromText($params['neu'][0]);
       
         }

         for($i=0;$i<count($params['name']);$i++){
            $mail=sprintf("%s <%s>",mb_encode_mimeheader($params['name'][$i]),$params['addr'][$i]);
            $this->setEmail($mail,$i);
         }
         for($i=0;$i<count($params['delete']);$i++){
            $this->delEmail($params['delete'][$i]);
         }
         if(isset($params['notiz'])){
             $this->setValue('NOTE',0,0,$params['notiz'][0]);
         }      
         $this->setValue('BDAY',0,0,date("d. F Y H:i:s"));
         $this->toFile();
       }
     }
       
}


class AddressBook extends Vcard{

   public $bookDir;
   public $bookName;
   public $vcards;

   public function AddressBook(){
     return $this;
   }

   public function setBook($bookDir){
      $this->bookDir=$bookDir;
      $this->readBook();
   }

   public function setBookName($name){
      $this->bookName=$name;
   }

   public function readBook(){

      $this->vcards=array();

      $i=0;
      //echo $this->basedir;
      if (is_dir($this->bookDir)) {
        if ($dh = opendir($this->bookDir)) {
          while (($file = readdir($dh)) !== false) {
            
            if(false!==strpos($file,'.vcf')){
               //echo "filename: $file\n<br>";            
               $temp=new Vcard($this->bookDir);
               $temp->setFileName($file);
               //$temp->setIndex($i);
               $temp->fromFile();
               $this->vcards[$i++]=$temp; 
            }
          }
          closedir($dh);
        }
      }
      return $this;
   }

   public function orderByFN(){

     $index=array();
     foreach($this->vcards as $key => $vcard){
       $index[$key]=$vcard->getValue('FN');
     }       
     array_multisort($index,$this->vcards);

   }
   
   public function editHtml(){
     echo "<div style='background-color:rgb(180,180,180)'>\n";
     echo "Eine <a href='vcard.php?file=newfile'>neue Liste</a> erstellen.<br><br>\n"; 
       echo "<b>Adressbuch</b><br>\n";

     echo "<form action='".$_SERVER['PHP_SELF']."'>\n";
     foreach($this->vcards as $vcard){
        if($vcard->getList()){
          echo "<input type='checkbox' name='delete' value='".$vcard->filename."'>";
          echo "<a href='vcard.php?file=".$vcard->filename."'>".$vcard->getValue('FN')."</a>";
          echo " (".count($vcard->value['EMAIL']).")";
          if(!$this->isCorrect()){echo " Enth&auml;lt inkorrekte Mailaddressen!";}
          echo "<br>\n";
        }
     }
     echo "<br><input type='submit' name='remove' value='Ausgew&auml;lte Listen l&ouml;schen'> (Achtung: ohne R&uuml;ckfrage!)<br>\n";
     echo "</form></div>\n";
   }

   public function saveHtml(){
       if(isset($_GET['remove'])){


         //*******************query******************************************
         $query  = explode('&', $_SERVER['QUERY_STRING']);
         $params = array();
         foreach( $query as $param ){
               list($name, $value) = explode('=', $param);
               $params[urldecode($name)][] = urldecode($value);
         }
         //***************************************************************


      
         foreach($params['delete'] as $key => $val){
             unlink($this->bookDir.$val);
             foreach($this->vcards as $key => $vcard){
               if($vcard->filename==$val){unset($this->vcards[$key]);}
             }
         }
       }
    }



} //class abook

class Mua extends AddressBook{

  public $users;
  public $db_opts;
  public $mail_opts;

  public $ok;
  public $mediaDir;

  public $baseDir;
  public $sessionDir;
  public $attachDir;

  public $statusText;

  public function __construct($db_opts,$mail_opts){

     parent::__construct();     

     $_SESSION['from']='';
     $_SESSION['to']='';
     $_SESSION['subject']='';
     $_SESSION['text']='';
     $_SESSION['user']=-1;
     $_SESSION['liste']=-1;
     $_SESSION['versand']=1;

     $this->db_opts=$db_opts;
     $this->mail_opts=$mail_opts;

     $this->ok=false;
     $this->statusText="&nbsp;";

     $this->baseDir=dirname($_SERVER['SCRIPT_FILENAME']).'/';
     $this->sessionDir=$this->baseDir.'tmp/'.session_id().'/';
     $this->attachDir=$this->sessionDir.'attach/';
     $this->mediaDir='/var/www/';

     if(!file_exists($this->sessionDir)){mkdir($this->sessionDir);}
     if(!file_exists($this->attachDir)){mkdir($this->attachDir);}
 

     return $this;
  }

  public function setUsers($users){
     $this->users=$users;
  }

  private function getIndex($file){
    foreach($this->vcards as $key => $vcard){
       if($vcard->filename==$file){$res=$key;}
    }
    return $key;
  }


   private function isOK(){
     $ok=true;
     $txt="";
     if($_SESSION['user']==-1){if($ok){$txt.="Es fehlen: ";}else{$txt.=", ";} $ok=false;$txt.="Absender";}
     if($_SESSION['liste']==-1){if($ok){$txt.="Es fehlen: ";}else{$txt.=", ";} $ok=false;$txt.="Empf&auml;nger";}
     if($_SESSION['subject']==""){if($ok){$txt.="Es fehlen: ";}else{$txt.=", ";} $ok=false;$txt.="Betreff";} 
     if($_SESSION['text']==""){if($ok){$txt.="Es fehlen: ";}else{$txt.=", ";} $ok=false;$txt.="Text";}

     $this->ok=$ok;
     if(!$ok){$txt="<span style='color:rgb(255,0,0)'>".$txt."</span>";$this->statusText=$txt;}

     return $ok;           
   }

   private function displayHtmlArea(){

      $cke= new CKeditor();
      $config=array();
      $config['toolbar']= array(
      //array('Cut','Copy','Paste','PasteText','-','Print'),
      array('Source','-','Bold', 'Italic', 'Underline','-','NumberedList','BulletedList','-','Outdent','Indent','Blockquote'),
      array('JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','Font','FontSize','TextColor'),
      array('Link','Unlink','Image','Table','HorizontalRule')
);
      $config['fullPage']=false;

      CKFinder::SetupCKEditor($cke);
      $cke->editor("text",$_SESSION['text'],$config);
   }

   private function displayFrom(){

      echo "<select name='user' size='1'>\n";
      foreach($this->users as $key => $val){
        echo "<option ";
        if($_SESSION['user']==$key){echo "selected";}
        echo " value='".$key."'>".$val."</option>\n";
      }
      echo "</select>\n";
   }

   private function displayTo(){
      echo "<select name='liste' size='1'>\n";
      echo "<option ";
      if($_SESSION['liste']==-1){echo "selected";}
      echo " value='-1'></option>\n";

      foreach($this->vcards as $key => $vcard){
         if($vcard->getList()){
           echo "<option ";
           if($_SESSION['liste']==$key){echo "selected";}
           echo " value='".$key."'>".$vcard->getValue('FN')."</option>\n";
        }
      }
      echo "</select>\n";
   }

   private function displaySubject(){
     echo "<input name='subject' value='".$_SESSION['subject']."' type='text' size='61'>";
   }

   private function displayAttach(){
     if ($dh = opendir($this->attachDir)) {
         while (($file = readdir($dh)) !== false) {
              if($file[0]<>"."){echo $file."<br>\n";}
	 }
         closedir($dh);
      }
      echo "<input name='attach' type='file' size='61'>";
      echo "<input name='upload' type='submit' value='Laden'>";
      echo "<input name='reset' type='submit' value='Anh&auml;nge entfernen'>\n";
   }

   private function displaySendButton(){

       echo "<select name='versand' size='1'>\n";

       echo "<option ";
       if($_SESSION['versand']=="0"){echo "selected ";} 
       echo "value='0'>Direkt (nicht benutzen!)</option>\n";

       echo "<option ";
       if($_SESSION['versand']=="1"){echo "selected ";} 
       echo "value='1'>Jetzt</option>\n";

       echo "<option ";
       if($_SESSION['versand']=="2"){echo "selected ";} 
       echo "value='2'>Am Abend</option>\n";
       echo "</select>\n";
       echo "<input name='senden' type='submit' value='senden'>\n";
   }

   public function editHtml(){
     echo "<div style='background-color:rgb(180,180,180)'>\n";
     echo "<form action='".$_SERVER['PHP_SELF']."' method='post' enctype='multipart/form-data'>\n";
     echo "<table width='100%'>\n";
     echo "<tr valign='top'><td align='right'>Von:</td><td>";$this->displayFrom(); echo "</td></tr>\n";
     echo "<tr valign='top'><td align='right'>Liste:</td><td>";$this->displayTo(); echo "</td></tr>\n";
     echo "<tr valign='top'><td align='right'>Betreff:</td><td>";$this->displaySubject();echo "</td></tr>\n";
     echo "<tr valign='top'><td align='right'>Text:</td><td>";$this->displayHtmlArea();echo "</td></tr>\n";
     echo "<tr valign='top'><td align='right'>Anhang:</td><td>";$this->displayAttach();echo "</td></tr>\n";
     echo "<tr><td></td><td>"; echo $this->statusText; echo "</td></tr>\n";
     echo "<tr valign='top'><td align='right'></td><td>";$this->displaySendButton(); echo "</td></tr>\n";
     echo "</table>\n";
     echo "</div></form>\n"; 
   }

   public function saveHtml(){
     //if(isset($_POST['from'])){$_SESSION['from']=$_POST['from'];}
     if(isset($_POST['subject'])){$_SESSION['subject']=$_POST['subject'];}
     if(isset($_POST['text'])){$_SESSION['text']=$_POST['text'];}
     if(isset($_POST['liste'])){$_SESSION['liste']=$_POST['liste'];}
     if(isset($_POST['versand'])){$_SESSION['versand']=$_POST['versand'];}
     if(isset($_POST['user'])){$_SESSION['user']=$_POST['user'];}
    
     if(isset($_POST['reset'])){
        //$_SESSIOM['text']="";
        //$_SESSION['subject']="";
        //$_SESSION['liste']=-1;
        //$_SESSION['user']="-1";
        //$_SESSION['versand']=1;
        system("rm -r ".$this->attachDir."*");
     }

     if(isset($_GET['file'])){
        $_SESSION['liste']=$this->getIndex($_GET['file']);
//echo "<br>************".$_GET['file']."*****************".$_SESSION['liste']."*************<br>";
     }

     if(isset($_FILES['attach'])){
        move_uploaded_file($_FILES['attach']['tmp_name'],$this->attachDir.$_FILES['attach']['name']);
     }

     if($_SESSION['liste']<>-1){ //read vcard
        $vcard=$this->vcards[$_SESSION['liste']];
//echo "<pre>";print_r($vcard);echo "</pre>";
        foreach($vcard->value['EMAIL'] as $key => $mail_addr){
          $this->to[]=$vcard->getValue('EMAIL',$key);
        }
        $name=$vcard->getValue('FN');
     }


     if((isset($_POST['senden']))and($_POST["senden"]=="senden")){
      if($this->isOK()){

        $from=$this->users[$_SESSION['user']];

        switch($_SESSION['versand']){
          case 0: $wait=-1;
                  break;
          case 1: $wait=0;
                  break;
          case 2: $now=date("H")+0;
                  if(($now>6)and($now<19)){$wait=3600*(19-$now);}else{$wait=300;}
                  break;
          }

          $mailer= new Mailer($this->db_opts,$this->mail_opts);

          $mailer->setUserId($_SESSION['user']);
          $mailer->setDelay($wait);
          
          //compose mail
          $mailer->setFrom($from);
          $mailer->setTo($this->to);
          $mailer->setSubject($_SESSION['subject']);
          $tt="<html><head></head><body>".$_SESSION['text']."</body></html>";
          $ttt=$mailer->html2text($tt);
//echo $ttt;
          $mailer->setText($ttt);
          $mailer->setHtml($tt,$this->mediaDir);
          $mailer->addAttach($this->attachDir);

          //send mail
//echo "******************<br><pre>";print_r($mailer);echo "</pre><br>*****************<br>";

          //testing
          $mailer->sendMail();

          //send same to self
          $f[0]=$from;
          $mailer->setTo($f);
          $new_subject='Mail mit Betreff <<'.$_SESSION['subject'].'>> wurde an die Liste <<'.$name.'>> verschickt';
          $mailer->setSubject($new_subject);

          //send!
          //testing
          $mailer->sendMail();

          $this->statusText="<span style='color:rgb(0,128,0)'>Die Mail wurde verschickt</span>\n";
        }
       } //if($_POST.....

   } //save html

} //class mua
