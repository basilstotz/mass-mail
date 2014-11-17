<?php
include_once('config.php');


function html2text($html){
  return shell_exec( "echo \"".$html."\"|html2text");
}

function get_images($html){
  global $prefix;
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


function send_mail($user_id,$from,$to,$subject,$html,$files,$to_queue=false,$wait=0,$delete=false){

  global $db_options;
  global $mail_options;
  global $prefix;

  $crlf = "\n";
  $mime = new Mail_mime($crlf);

  //compose mail from $html
  $mime->setTXTBody(html2text($html));
  $mime->setHTMLBody($html);
  $bilder=get_images($html);
  for($i=0;$i<count($bilder);$i++){
    $mime->addHTMLimage($prefix.$bilder[$i],mime_content_type($prefix.$bilder[$i]),$bilder[$i],true);
  }

  for($i=0;$i<count($files);$i++){
    $mime->addAttachment($files[$i],mime_content_type($files[$i]));
  }

  $hdrs = array(
              'From'    => $from,
              'To'      => $to,
              'Subject' => $subject
              );



  //do not ever try to call these lines in reverse order
  $body = $mime->get();
  $hdrs = $mime->headers($hdrs);

  if($to_queue){
    //send to queue
    $sec_to_send=$wait;
    $delete_after_send=$delete;
    $mail_queue=& new Mail_Queue($db_options,$mail_options);
    $mail_id=$mail_queue->put($from,$to,$hdrs,$body,$sec_to_send,$delete_after_send,$user_id);
  }else{
    //send
    $mail =& Mail::factory('sendmail');
    $mail->send($to, $hdrs, $body);
    $mail_id=-1;
  }


  return $mail_id;
}


?>