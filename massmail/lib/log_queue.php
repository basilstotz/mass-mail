<?php
require_once('MDB2.php');



function log_queue($smtp_user,$mail_user,$from,$to){


  $mdb2=& MDB2::factory('mysql://root:AmEsadS@localhost/mail');
  if (PEAR::isError($mdb2)){die($mdb2->getMessage());}
  $mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);


  $sql="insert into mail_log set smtp_user='".$smtp_user."', mail_user='".$mail_user."', sender='".$from."', recipient='".$to."';";
   $res =& $mdb2->query($sql);
  if (PEAR::isError($res)) {die($res->getMessage());}
  //echo $sql."\n";
  //bye bye
  $mdb2->disconnect();
  return true;
}


?>