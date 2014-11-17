<?php
require_once('MDB2.php');



function stats_queue(){


  $mdb2=& MDB2::factory('mysql://root:AmEsadS@localhost/mail');
  if (PEAR::isError($mdb2)){die($mdb2->getMessage());}
  $mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);

  $sql[0]="select mail_user as user,count(id) as mails, count(distinct recipient) as direcip from mail_recent_log group by mail_user";
  $sql[1]="select smtp_user as user,count(id) as mails, count(distinct recipient) as direcip from mail_recent_log group by smtp_user";

  $name[0]="MailUser";
  $name[1]="SmtpUser";

  for($i=0;$i<2;$i++){
     $res =& $mdb2->query($sql[$i]);
     if (PEAR::isError($res)) {die($res->getMessage());}

     echo "<table>\n";
     echo "<tr><td>".$name[$i]."</td><td>Mails</td><td>Recipients</td></tr>\n";
     while ($row = $res->fetchRow()) {
       echo "<tr>";
       foreach($row as $value){
         echo "<td align='right'>".$value."</td>";
       }
       echo "</tr>\n";      
     }
     echo "</table>\n";
     echo "<br><br>\n";
  }
  //bye bye
  $mdb2->disconnect();
  return true;
}



?>