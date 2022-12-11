<?php
include("config.php");

//Avtorizacija čez url ali čez HTTPS auth
if(!empty($_GET['auth_key'])) {
	$key = mysqli_real_escape_string($mysqli, $_GET['auth_key']);
	
	$q = mysqli_query($mysqli, "select * from sklepnik_dogodki where admin_pass_hash = '$key'");
	if(mysqli_num_rows($q) != 1) die("Authorization failed.");
	
	$dogodek_row = mysqli_fetch_object($q);
	$dogodek_id = $dogodek_row->id;
}
else include("login.php");

//Je ključ uporabnika podan?
$u = mysqli_real_escape_string($mysqli, $_GET['u']);
if(empty($u)) die("Neznan uporabnik #1.");

$query = mysqli_query($mysqli, "select * from sklepnik_delegati where login_key = '$u' and dogodek_id = '$dogodek_id'");
if(mysqli_num_rows($query) != 1) die("Neznan uporabnik #2.");
	
//očitno uporabnik obstaja...
$user_row = mysqli_fetch_object($query);

//Import knjižnice
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPmailer/src/Exception.php';
require 'PHPmailer/src/PHPMailer.php';
require 'PHPmailer/src/SMTP.php';

//Create a new PHPMailer instance
$phpMailer = new PHPMailer;

//Tell PHPMailer to use SMTP
$phpMailer->isSMTP();

//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$phpMailer->SMTPDebug = 0;
$phpMailer->CharSet = 'UTF-8';


/** Nastavitve poštnih strežnikov **/

//Set the hostname of the mail server
$phpMailer->Host = Sklepnik_SMTP_Host;

//Set the SMTP port number - likely to be 25, 465 or 587
$phpMailer->Port = Sklepnik_SMTP_Port;


$phpMailer->SMTPAuth = true;
$phpMailer->SMTPSecure = Sklepnik_SMTP_Encryption;

$phpMailer->Username = Sklepnik_SMTP_Username;
$phpMailer->Password = Sklepnik_SMTP_Password; 

$phpMailer->setFrom('zts.it@taborniki.si', 'ZTS IT ekipa');
$phpMailer->addReplyTo('zts.it@taborniki.si', 'zts.it@taborniki.si');

//Headerji maila
$headers = array(
	'Content-Type: text/html; charset=UTF-8',
	'From: ZTS IT ekipa <zts.it@taborniki.si>'
);

//Komu nej to pošljem, ejga?
$phpMailer->addAddress($user_row->email);

//Vsebina maila in zadeva
$mail_subject = $dogodek_row->mail_zadeva;
$mail_html = $dogodek_row->mail_html;

//tekstovna oblika (če je na voljo, sicer generiraj avtomatsko iz HTML)
$mail_text = $dogodek_row->mail_text;
if(empty($mail_text)) $mail_text = strip_tags($mail_html);

//zamenjaj pomembne stvari v templejtu:
$tpl_replace = array(
	'{delegat-ime}' => $user_row->ime,
	'{delegat-priimek}' => $user_row->priimek,
	'{delegat-rod}' => $user_row->rod,
	'{delegat-rod-kratica}' => $user_row->rod_kratica,
	'{delegat-obmocje}' => $user_row->obmocje,
	'{delegat-obmocje-kratica}' => $user_row->obmocje_kratica,
);

foreach($tpl_replace as $search=>$replace) {
	$mail_html = str_replace($search, $replace, $mail_html);
	$mail_subject = str_replace($search, $replace, $mail_subject);
	$mail_text = str_replace($search, $replace, $mail_text);
}

$phpMailer->Subject = $mail_subject;


//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$phpMailer->msgHTML($mail_html, __DIR__);

//Replace the plain text body with one created manually
$phpMailer->AltBody = $mail_text;
 
echo $mail_text;
die;

//send the message, check for errors
if (!$phpMailer->send()) {
    echo 'Mailer Error: ' . $phpMailer->ErrorInfo; 
} else {
	
	//Vse je ok :)
    echo("ok");
	
	//Zabeleži poslano
	//file_put_contents("sent-log.txt", date('Y-m-d H:i:s')."\t".$data['mail_to']."\n", FILE_APPEND);
}
