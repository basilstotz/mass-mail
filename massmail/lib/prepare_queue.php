<?php
require_once('MDB2.php');



function prepare_queue($maxmail=90,$intervall=86400,$timeshift=3600,$timeskew=300){


  $mdb2=& MDB2::factory('mysql://root:AmEsadS@localhost/mail');
  if (PEAR::isError($mdb2)){die($mdb2->getMessage());}
  $mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);

  // delete all mail from queue which are sent $intervall seconds before
  $sql='delete from mail_queue where sent_time is not null and (unix_timestamp()-unix_timestamp(sent_time))>'.$intervall.';';
  $res =& $mdb2->query($sql);
  if (PEAR::isError($res)) {die($res->getMessage());}

  //count sent Mails per user....
  $sql="select id_user,count('id_user') as num from mail_queue where sent_time is not null group by id_user";
  $res =& $mdb2->query($sql);
  if (PEAR::isError($res)) {die($res->getMessage());}

  while ($row = $res->fetchRow()) {
    // ...and increment time_to_send if $maxmail exceeded
    if($row['num']>=$maxmail){
       $id_user=$row['id_user'];
       $sql="select id from mail_queue where time_to_send<now()+interval ".$timeskew." second and sent_time is null and id_user='".$id_user."'";
       $res2 =& $mdb2->query($sql);
       if (PEAR::isError($res2)) {die($res2->getMessage());}
       while($row2=$res2->fetchRow()){
            $sql="update mail_queue set time_to_send=now()+interval ".$timeshift." second where id='".$row2['id']."'";
            $res3 =& $mdb2->query($sql);
            if (PEAR::isError($res3)) {die($res3->getMessage());}
       }
    }
  }

  //bye bye
  $mdb2->disconnect();
  return true;
}



?>