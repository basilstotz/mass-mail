<?php

require_once('File/IMC.php');

function read_vcard($file){
   $parse=File_IMC::parse('vCard');
   $info=$parse->fromFile($file);
   return $info;
}

function split_mail($text){

    $res=array();

             if(false===strpos($text,"<")){
               $name="";
               $mail=$text;
             }else{
               $mail=$text;
               $mail=strrchr($mail,"<");
               //$mail=substr($mail,1,strlen($mail)-1);

               $name=substr($text,0,strlen($text)-strlen($mail));
             }
             $mail=str_replace(":","",$mail);
             $mail=str_replace(" ","",$mail);
             $mail=str_replace("<","",$mail);
             $mail=str_replace(">","",$mail);
             $mail=str_replace("'","",$mail);
             $mail=strtolower($mail);

             $name=str_replace("\"","",$name);
             $name=str_replace("'","",$name);

             $res['name']=$name;
             $res['mail']=$mail;

             return $res;
}



function read_lists($dir){

  $j=0;
  if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            //echo "filename: $file\n<br>";
            if(false!==strpos($file,'.vcf')){
               $info=read_vcard($dir.$file);
               if(isset($info['VCARD'][0]['X-EVOLUTION-LIST'][0]['value'][0][0])){
                  if($info['VCARD'][0]['X-EVOLUTION-LIST'][0]['value'][0][0]=="TRUE"){
                      $vcards['name'][$j]=$info['VCARD'][0]['FN'][0]['value'][0][0];
                      $vcards['file'][$j]=$file; 
                      $j++;
                  }
               }
            }
        }
        closedir($dh);
     }
  }
  array_multisort($vcards['name'],$vcards['file']);
  return($vcards);

}




function fetch($info,$type){

  $res="";
  $emails=$info['VCARD'][0]['EMAIL'];
  $names=$info['VCARD'][0]['N'][0]['value'];
  mb_internal_encoding('UTF-8');

 switch($type){
    case "vcf":
       $res.=sprintf("%s\r\n","VCARD:BEGIN");
       $res.=sprintf("%s\r\n","VERSION:3.0");
       $res.=sprintf("%s\r\n","X-EVOLUTION-LIST:TRUE");
       $res.=sprintf("FN:%s\r\n",$info['VCARD'][0]['FN'][0]['value'][0][0]);

       $res.=sprintf("%s","N:");
       for($i=0;$i<count($names);$i++){
          if($i==0){
             $res.=sprintf(";%s",$names[$i][0]);
          }else{
             $res.=sprintf(";%s",$names[$i][0]);
          }
       }
       $res.=sprintf("%s","\r\n");

       for($i=0;$i<count($emails);$i++){
          $res.=sprintf("EMAIL:%s\r\n",$emails[$i]['value'][0][0]);
       }

       $res.=sprintf("%s\r\n","VCARD:END");

       break;

    case "tex":
       $res.=sprintf("%s","\\documentclass{scrartcl}\n");
       $res.=sprintf("%s","\\usepackage[utf8]{inputenc}\n");
       //$res.=sprintf("%s","\\pagestyle{empty}\n");
       $res.=sprintf("%s","\\begin{document}\n");
       $res.=sprintf("%s","\\section*{".$info['VCARD'][0]['FN'][0]['value'][0][0]."}\n");
       //$res.=sprintf("%s","\\begin{tabular}{ll}\n");

       for($i=0;$i<count($emails);$i++){
           $txt=split_mail($emails[$i]['value'][0][0]);
           $res.=sprintf("\\textbf{%s} %s\\\\\n",mb_decode_mimeheader($txt['name']),str_replace("_","\_",$txt['mail']));
       }

       //$res.=sprintf("%s","\\end{tabular}\n");
       $res.=sprintf("%s","\\end{document}\n");

       break;

    case "csv":
       for($i=0;$i<count($emails);$i++){
           $txt=split_mail($emails[$i]['value'][0][0]);
           $res.=sprintf("\"%s\";%s\n",mb_decode_mimeheader($txt['name']),str_replace(" ","",$txt['mail']));
       }

       break;

    case "html":
       $res.=sprintf("<h3>%s</h3>\n",$info['VCARD'][0]['FN'][0]['value'][0][0]);
       $res.="<h4>";
       for($i=0;$i<count($names);$i++){
           $res.=sprintf("%s ",htmlspecialchars($names[$i][0]));
       }
       $res.="</h4>\n";
       $res.="<table>\n";
       for($i=0;$i<count($emails);$i++){
           $txt=split_mail($emails[$i]['value'][0][0]);
           $res.=sprintf("<tr><td><b>%s</b></td><td>%s</td></tr>\n",mb_decode_mimeheader($txt['name']),$txt['mail']);
       }
       $res.="</table>\n";
       break;

    case "edit":
       if(!isset($neu)){$neu="";}
       $res.=sprintf("<br><input type='text' name='fullname' size='80' value='%s'><br>",$info['VCARD'][0]['FN'][0]['value'][0][0]);
       for($i=0;$i<count($names);$i++){
           $res.=sprintf("<input type='text' name='names' value='%s'>\n",htmlspecialchars($names[$i][0]));
       }
       $res.="<br>";
       $res.=sprintf("<textarea name='neu' cols='120' rows='10'>%s</textarea>\n",$neu);
       $res.="<table>\n";
       foreach($emails as $email){
       //for($i=0;$i<count($emails);$i++){
           $txt=split_mail($email['value'][0][0]);
           $res.="<tr>";
           $res.=sprintf("<td><input type='checkbox' name='delete' value='%d'></td>",$i);
           $res.=sprintf("<td><input type='text' name='texts' size='50' value='%s'></td>",mb_decode_mimeheader($txt['name']));
           $res.=sprintf("<td><input type='text' name='emails' size='30' value='%s'></td>",$txt['mail']);
           $res.="</tr>\n";
       }
       $res.="</table>\n";
       break;

    default: echo "unbekannt<br>"; break;
  }

  return $res;
}

function put(&$info){


       if(!isset($_GET['fullname'])){return;}

            $query  = explode('&', $_SERVER['QUERY_STRING']);
            $params = array();

            foreach( $query as $param ){
               list($name, $value) = explode('=', $param);
               $params[urldecode($name)][] = urldecode($value);
            }


       //echo "<pre>";print_r($params);echo "</pre>";
       
       //löschen
       if(isset($params['delete'])){
          for($i=0;$i<count($params['delete']);$i++){
             //unset($params['emails'][$params['delete'][$i]]);
             unset($info['VCARD'][0]['EMAIL'][$params['emails'][$i]]['value'][0][0]);
          }
       }

       //hinzufügen
       $neu=$params['neu'][0];
       $neu=str_replace("\n",",",$neu);
       $neu=str_replace(",,",",",$neu);
       $neue_mails=explode(",",$neu);
       for($i=0;$i<count($neue_mails);$i++){
         $info['VCARD'][0]['EMAIL'][]['value'][0][0]=mb_encode_mimeheader($neue_mails[$i]);
       }

       //kopieren
       $info['VCARD'][0]['FN'][0]['value'][0][0]=$params['fullname'][0];
       for($i=0;$i<count($params['names']);$i++){
         $info['VCARD'][0]['N'][0]['value'][$i][0]=$params['names'][$i];
       }
       for($i=0;$i<count($params['emails']);$i++){
         if(isset($params['emails'][$i])){
            $info['VCARD'][0]['EMAIL'][$i]['value'][0][0]=$params['texts'][$i]." <".$params['emails'][$i].">";
         }
       }

}

?>
