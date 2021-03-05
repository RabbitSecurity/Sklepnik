<?php
include("config.php");

//Avtorizacija čez url ali čez HTTPS auth
if(!empty($_GET['auth_key'])) {
	$key = mysql_real_escape_string($_GET['auth_key']);
	
	$q = mysql_query("select * from sklepnik_dogodki where admin_pass_hash = '$key'");
	if(mysql_num_rows($q) != 1) die("Authorization failed.");
	
	$dogodek_row = mysql_fetch_object($q);
	$dogodek_id = $dogodek_row->id;
}
else include("login.php");

//Je ključ uporabnika podan?
$u = mysql_real_escape_string($_GET['u']);
if(empty($u)) die("Neznan uporabnik #1.");

$query = mysql_query("select * from sklepnik_delegati where login_key = '$u' and dogodek_id = '$dogodek_id'");
if(mysql_num_rows($query) != 1) die("Neznan uporabnik #2.");
	
//očitno uporabnik obstaja...
$user_row = mysql_fetch_object($query);



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

//zamenjaj pomembne stvari v templejtu:
$ime = $user_row->ime." ".$user_row->priimek;

//Zadeva
$phpMailer->Subject = 'Povezava za online glasovanje';

//Vsebina maila
$mail_html = "Pozdravljen_a $ime !<br/><br/>Pošiljamo ti povezavo do on-line glasovanja:<br/>
<a href='".Sklepnik_URL."registracija.php?u=$u'>https://bostjan.info/sklepnik/registracija.php?u=$u</a><br/>
<br/>
<b>Povezave ne smeš deliti</b>, saj je unikatna in omogoča glasovanje v tvojem imenu.<br/>
<br/>
Vsi ostali lahko glasovanje v živo spremljajo <a href='".Sklepnik_URL."?dogodek=".$dogodek_row->access_key."'>tukaj</a>.
<br/><br/>
Ekipa sklepnika.";

//Tekstovna verzija
$mail_text = "Pozdravljen_a $ime !\n\nPošiljamo ti povezavo do on-line glasovanja:
".Sklepnik_URL."registracija.php?u=$u

Povezave ne smeš deliti, saj je unikatna in omogoča glasovanje v tvojem imenu!

Vsi ostali lahko glasovanje v živo spremljajo tukaj:
".Sklepnik_URL."?dogodek=".$dogodek_row->access_key."

Ekipa sklepnika.";


//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$phpMailer->msgHTML($mail_html, __DIR__);

//Replace the plain text body with one created manually
$phpMailer->AltBody = $mail_text;
 


//send the message, check for errors
if (!$phpMailer->send()) {
    echo 'Mailer Error: ' . $phpMailer->ErrorInfo; 
} else {
	
	//Vse je ok :)
    echo("ok");
	
	//Zabeleži poslano
	//file_put_contents("sent-log.txt", date('Y-m-d H:i:s')."\t".$data['mail_to']."\n", FILE_APPEND);
}
