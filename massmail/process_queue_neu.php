#!/usr/bin/php -q
<?php

require_once('config.php');

$quota_max=96;
$quota_time=86400;
$wait=10;



function clean_queue($keep=0){

  global $quota_time;
  global $mdb2;

  if($keep<$quota_time){$keep=$quota_time;}

// delete all mail from queue which are sent $intervall seconds before
  $sql='delete from mail_queue where sent_time is not null and (unix_timestamp()-unix_timestamp  (sent_time))>'.$keep.';';
  $res =& $mdb2->query($sql);
  if (PEAR::isError($res)) {die($res->getMessage());}
}

function get_quota($sender){

  global $mdb2;
  global $quota_time;

  $sql="select count(id) as num from mail_queue \\
         where (sent_time is not null) \\ 
            and ((unix_timestamp()-unix_timestamp(sent_time))<".$quota_time.") \\
            and (sender = ".$sender.") \\
         group by sender";

  $res =& $mdb2->query($sql);
  if (PEAR::isError($res)) {die($res->getMessage());}

  if($row=$res->fetchRow()){
    return($row['num']);
  }else{
    return(0);
  }
}

function send_queue($limit=0){

  global $mdb2;
  global $wait;
  global $db_options;
  global $mail_options;
  global $quota_max;

//get sorted mail_queue
  $sql="select id,sender from mail_queue \\
         where sent_time is null \\
         order by user_id asc, time_to_send asc";
  $res=& $mbd2->query($sql);
  if (PEAR::isError($res)) {die($res->getMessage());}

/* we use the db_options and mail_options from the config again  */
  $mail_queue =& new Mail_Queue($db_options, $mail_options);

  $count=0;
  while($row=$res->fetch_row()and(($count<$limit))or($limit==0)){
    if(get_quota($row['sender'])<$quota_max){
       $mail_queue->SendMailById($row['id']);
       if($limit>0){$count++;}
       sleep($wait/2+mt_rand(0,$wait));
    }
  }
  return $count;
}


function connect_queue(){

  global $mdb2;

//connect to db
  $mdb2=& MDB2::factory('mysql://root:AmEsadS@localhost/mail');
  if (PEAR::isError($mdb2)){die($mdb2->getMessage());}
  $mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);
}

function set_quota($max,$time=86400){

  global $quota_time;
  global $quota_max;

  $quota_max=$max;
  $quota_time=$time;
}

function set_wait($time){
  global $wait;
  $wait=$time;
}

set_wait(10);
set_quota(97,86400);

connect_queue();

clean_queue();
$num_sent=send_queue(4);

exit(0);

?>
