<?php
include("config.php");
include("login.php");

//Je ključ uporabnika podan?
$u = mysql_real_escape_string($_GET['u']);
if(empty($u)) die("Neznan uporabnik #1.");

$query = mysql_query("select * from sklepnik_delegati where login_key = '$u'");
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
$phpMailer->Host = 'smtp.office365.com';

//Set the SMTP port number - likely to be 25, 465 or 587
$phpMailer->Port = 587;


$phpMailer->SMTPAuth = true;
$phpMailer->SMTPSecure = "starttls";

$phpMailer->Username = 'zts.it@taborniki.si';
$phpMailer->Password = '********'; 

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
<a href='https://bostjan.info/sklepnik/registracija.php?u=$u'>https://bostjan.info/sklepnik/registracija.php?u=$u</a><br/>
<br/>
<b>Povezave ne smeš deliti</b>, saj je unikatna in omogoča glasovanje v tvojem imenu.<br/>
<br/>
Vsi ostali lahko glasovanje v živo spremljajo <a href='https://bostjan.info/sklepnik/?dogodek=c4ca4238a0b923820dcc509a6f75849b'>tukaj</a>.
<br/><br/>
Ekipa sklepnika.";

//Tekstovna verzija
$mail_text = "Pozdravljen_a $ime !\n\nPošiljamo ti povezavo do on-line glasovanja:
https://bostjan.info/sklepnik/registracija.php?u=$u

Povezave ne smeš deliti, saj je unikatna in omogoča glasovanje v tvojem imenu!

Vsi ostali lahko glasovanje v živo spremljajo tukaj:
https://bostjan.info/sklepnik/?dogodek=c4ca4238a0b923820dcc509a6f75849b

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
