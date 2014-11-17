#!/usr/bin/php -q
<?php

require_once('config.php');


class pq_mail_queue extends mail_queue {

public $quota_max;
public $quota_time;
public $wait;

private $mdb2;
private $db_opt;
private $mail_opt;

public function __construct($db_options,$mail_options){

  $db_opt=$db_options;
  $mail_opt=$mail_options;

  //parent construktor
  parent::__construct($db_options,$mail_options);

  //set defaults
  $this->quota_max=96;
  $this->quota_time=86400;
  $this->wait=10;

  //connect to db
  //$this->mdb2=& MDB2::factory('mysql://root:AmEsadS@localhost/mail');
  $this->mdb2=& MDB2::factory($db_options['dsn']);
  if (PEAR::isError($this->mdb2)){die($this->mdb2->getMessage());}
  $this->mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);

  return $this;


public function clean_queue($keep=0){


  if($keep<$this->quota_time){$keep=$this->quota_time;}

// delete all mail from queue which are sent $intervall seconds before
  $sql="delete from ".$this->db_opt['mail_tablë́']." where sent_time is not null and (unix_timestamp()-unix_timestamp  (sent_time))>".$keep.";";
  $res =& $this->mdb2->query($sql);
  if (PEAR::isError($res)) {die($res->getMessage());}
}

public function get_quota($sender){

  $sql="select count(id) as num from ".$this->db_opt['mail_tablë́']." \\
         where (sent_time is not null) \\ 
            and ((unix_timestamp()-unix_timestamp(sent_time))<".$this->quota_time.") \\
            and (sender = ".$sender.") \\
         group by sender";

  $res =& $this->mdb2->query($sql);
  if (PEAR::isError($res)) {die($res->getMessage());}

  if($row=$res->fetchRow()){
    return($row['num']);
  }else{
    return(0);
  }
}

public function send_queue($limit=0){

//get sorted mail_queue
  $sql="select id,sender from mail_queue \\
         where sent_time is null \\
         order by user_id asc, time_to_send asc";
  $res=& $this->mbd2->query($sql);
  if (PEAR::isError($res)) {die($res->getMessage());}

/* we use the db_options and mail_options from the config again  */
//  $mail_queue =& new Mail_Queue($db_options, $mail_options);

  $count=0;
  while($row=$res->fetch_row()and(($count<$limit))or($limit==0)){
    if($this->get_quota($row['sender'])<$this->quota_max){
       $this->SendMailById($row['id']);
       if($limit>0){$count++;}
       sleep($this->wait/2+mt_rand(0,$this->wait));
    }
  }
  return $count;
}


public function set_quota($max,$time=86400){

  $this->quota_max=$max;
  $this->quota_time=$time;
}

public function set_wait($time){
 
  $this->wait=$time;
}

}//class


$mail_queue=& new pq_mail_queue($db_options,$mail_options);

$mail_queue->set_wait(10);
$mail_queue->set_quota(97,86400);

$mail_queue->clean_queue();
$mail_queue->send_queue(4);

exit(0);

?>
