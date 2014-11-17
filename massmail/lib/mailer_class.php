<?php

require_once('Mail.php');
require_once('Mail/mime.php');
require_once('Mail/Queue.php');
require_once('MDB2.php');

class Mailer {

  public $db_options;
  public $mail_options;
  public $user_id;

  public $from; //string
  public $to; //array of string

  public $subject; //string

  public $text; //string

  public $html; //string
  public $images; //array of string
  public $mediaDir;

  public $attach;      //array of string

  public $wait; //integer
  public $mode; //string



  public function Mailer($db_options,$mail_options){
    $this->db_options=$db_options;
    $this->mail_options=$mail_options;
    $this->from="";
    $this->to=array();
    $this->subject="";
    $this->text="";
    $this->html="";
    $this->images=array();
    $this->attach=array();
    $this->wait=0;
    $this->mode='single';
    return $this;
  }

    

  public function html2text($html){
     file_put_contents("/tmp/html",$html);
     $t=shell_exec( "/usr/bin/html2text /tmp/html");
     return $t;
  }

  private function getImages($html){
    $bilder=array();
    $i=0;
    while(false!==$html=strstr($html,"<img")){
      $html=strstr($html,"src");

      $res=strstr($html," ",true);
      $res=substr($res,0,strlen($res)-1);
      $res=substr($res,5);   

      $html=strstr($html," ",false);
 
      $found=false;
      for($j=0;$j<count($bilder);$j++){
         if($res==$bilder[$j]){$found=true;}
      }
      if(!$found){$bilder[$i++]=$res;}
    }
    return $bilder;
  }

//********************************************************************************3


  public function setUserId($user_id){ 
   $this->user_id=$user_id;
  }

  public function setFrom($from){ 
   $this->from=$from;
  }

  public function setTo($to){
    $this->to=array();
    foreach($to as $addr){
      $this->to[]=$addr;
    }
  }



  public function setText($text){
    $this->text=$text;
  }

  public function setHtml($html,$mediaDir){
    $this->html=$html;
    $this->mediaDir=$mediaDir;
    $bilder=$this->getImages($html);
    foreach($bilder as $bild){
       $this->images[]=str_replace("//","/",$bild);
    }
  }

  public function addAttach($attachDir){
    if(file_exists($attachDir)){
    if ($dh = opendir($attachDir)) {
      while (($file = readdir($dh)) !== false) {
        if($file[0]<>"."){
          $this->attach[]=$attachDir.$file;
        }
      }
      closedir($dh);
    }
  }

    
  }

  public function setSubject($text){
    $this->subject=$text;
  }

  // $mode: 'single','bulk'
  public function setMode($mode='single'){
    $this->mode=$mode;

  }

  // $delay: 0< ->direct; 0>= ->queued   
  public function setDelay($delay=0){
     $this->delay=$delay;
  }

 
  private function sendMailTo($to){

     $mime = new Mail_mime("\n");

     //compose mail 
//     if($this->html<>""){
//       if($this->text==""){
//         $mime->setTXTBody($this->html2text($this->html));
//       }else{
//         $mime->setTXTBody($this->text);
//       }
//       $mime->setHTMLBody($this->html);
//       // bilder dazufuegen
//       foreach($this->images as $image){
//          $mime->addHTMLimage($image,mime_content_type($image),$image,true); 
//       }
//     }else{
//       $mime->setTXTBody($this->text);
//     }


     $ty=$this->html2text($this->html);
     $mime->setTXTBody($ty);
     $mime->setHTMLBody($this->html);
     foreach($this->images as $image){
       $mime->addHTMLimage(file_get_contents($this->mediaDir.$image),mime_content_type($this->mediaDir.$image),$image,false);
     }

    //attach
    foreach($this->attach as $file){
      $mime->addAttachment($file,mime_content_type($file));
    }

         $hdrs = array(
                  'From'    => $this->from,
                  'To'      => $to,
                  'Subject' => $this->subject
                  );
         $body = $mime->get();
         $hdrs = $mime->headers($hdrs);

         if($this->delay>=0){ 
             $mail_queue =& new Mail_Queue($this->db_options,$this->mail_options);
             $mail_id=$mail_queue->put($this->from,$to,$hdrs,$body,$this->delay,$this->user_id);
         }else{
             $mail=& Mail::factory('sendmail');
             $mail->send($to,$hdrs,$body);
             $mail_id=-1;
         }
         return $mail_id;
  }


  public function sendMail(){


//echo "<pre>";print_r($this);echo "</pre>";
    //
    if($this->mode=='single'){
       foreach($this->to as $to){
         $this->sendMailTo($to);
       }
    }else{ 
       $to=implode(",",$this->to);
       $this->sendMailTo($to);
    }
  }

}

?>