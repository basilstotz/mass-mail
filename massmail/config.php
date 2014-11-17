<?php
require_once('Mail.php');
require_once('Mail/mime.php');
require_once('Mail/Queue.php');
require_once('MDB2.php');

$db_options = array(
    'type'        => 'mdb2',
    'dsn'         => 'mysql://root:AmEsadS@localhost/mail',
    'mail_table'  => 'mail_queue'
);

// here are the options for sending the messages themselves
// these are the options needed for the Mail-Class, especially used for Mail::factory()
$mail_options['driver']    = 'sendmail';

//   '1001' => 'roland.bauer@vpod-basel.ch',

$users= array(
     '-1' => '',
   '1000' => 'basil.stotz@vpod-basel.ch',
   '1002' => 'edith.jaeckle@vpod-basel.ch',
   '1003' => 'franziska.sager@vpod-baselch',
   '1004' => 'heidi.mueck@vpod-basel.ch',
   '1005' => 'marianne.meyer@vpod-basel.ch',
   '1006' => 'matthias.scheurer@vpod-basel.ch',
   '1007' => 'susanne.nese@vpod-basel.ch'
   );

$mediaDir='/var/www/';
$bookdir='/var/webdav/kontakte/';
$bookname='vpod-global';

?>
