<?php
require_once('MDB2.php');

function count_queue($smtp_user){
  $mdb2=& MDB2::factory('mysql://root:AmEsadS@localhost/mail');
  if (PEAR::isError($mdb2)){die($mdb2->getMessage());}
  $mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);

  $sql="select count(id) as num from mail_recent_log where smtp_user='".$smtp_user."' group by smtp_user";
  $res =& $mdb2->query($sql);
  if (PEAR::isError($res)) {die($res->getMessage());}

  $row=$res->fetchRow();
  //bye bye
  $mdb2->disconnect();
  return $row['num'];
}

?>

