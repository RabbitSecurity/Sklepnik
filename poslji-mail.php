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

//zamenjaj pomembne stvari v templejtu:
$ime = $user_row->ime." ".$user_row->priimek;



/*
//Zadeva
$phpMailer->Subject = 'Povezave za online skupščino';

//dodaj attachment
$phpMailer->addAttachment("templates/Kratka-navodila-za-uporabo-programov-Zoom-Padlet-Sklepnik.pdf");

//Vsebina maila
$mail_html = "Pozdravljen_a $ime!<br/>
<br/>
Veseli nas, da si se prijavil_a na forum. <br/>
<br/>
Forum bo potekal preko Zooma, do katerega lahko dostopaš preko linka:<br/>
<a href='https://zoom.us/j/99656305509?pwd=bFVOQkIvaXVURDBqWVB5eUpRWGdoUT09'>https://zoom.us/j/99656305509?pwd=bFVOQkIvaXVURDBqWVB5eUpRWGdoUT09</a><br/>
<br/>
Prosimo te, da se na zoomu ustrezno poimenuješ: $ime, $user_row->rod_kratica, $user_row->obmocje_kratica - delegat. Za preverjanje prisotnosti vas prosimo, da se pridužite seji s prižgano kamero.<br/>
Priporočamo, da Zoom aplikacijo posodobite na najnovejšo verzijo pred Odprtim Forumom.
<br/>
<br/>
Vprašanje lahko postavite s pomočjo padleta:<br/>
<a href='https://padlet.com/ztsit/edui0uy7ui9kv8vz'>https://padlet.com/ztsit/edui0uy7ui9kv8vz</a><br/> 
<br/>
V padlet vpišite svoja <b>ime in priimek, kratico rodu ter na katero temo želite postaviti vprašanje</b>. Ko bo čas za vprašanja, vas bo moderator pozval k besedi. Takrat prižgite mikrofon, povejte svoje vprašanje in nato ugasnite mikrofon. <br/> 
<br/>
Na Forumu se boste lahko tudi preizkusili v uporabi Sklepnika, ki ga bomo na skupščini uporabili za sprejemanje sklepov. V ta namen imate tukaj povezavo <br/>
<a href='".Sklepnik_URL."registracija.php?u=$u'>".Sklepnik_URL."registracija.php?u=$u</a><br/>
<br/>
preko katere boste lahko glasovali na poskusnih sklepih na Forumu. Ker povezava omogoča glasovanje v vašem imenu, je <b>ne delite</b> z drugimi osebami.<br/>
<b>POZOR</b>: Ta link velja samo za ta Forum. Za skupščino boste dobili drugo povezavo in ta ne bo več delovala. <br/>
<br/>
Podrobnejša navodila za uporabo Zooma in Sklepnika so v priponki.<br/> 
<br/>
V kolikor boste imeli težavo z odpiranjem Zoom seje za forum, lahko pokličete na <a href='+38641537822'>041 537 822</a> (Urša Primožič). <br/>
<br/>
Se vidimo na forumu! <br/>
Ekipa IT podpore skupščine in forumov";


//Mal v txt obliki
$mail_txt = "Pozdravljen_a $ime!

Veseli nas, da si se prijavil_a na forum.

Forum bo potekal preko Zooma, do katerega lahko dostopaš preko linka:
https://zoom.us/j/99656305509?pwd=bFVOQkIvaXVURDBqWVB5eUpRWGdoUT09

Prosimo te, da se na zoomu ustrezno poimenuješ: $ime, $user_row->rod_kratica, $user_row->obmocje_kratica - delegat. Za preverjanje prisotnosti vas prosimo, da se pridužite seji s prižgano kamero.
Priporočamo, da Zoom aplikacijo posodobite na najnovejšo verzijo pred Odprtim Forumom.

Vprašanje lahko postavite s pomočjo padleta:
https://padlet.com/ztsit/edui0uy7ui9kv8vz

V padlet vpišite svoja <b>ime in priimek, kratico rodu ter na katero temo želite postaviti vprašanje</b>. Ko bo čas za vprašanja, vas bo moderator pozval k besedi. Takrat prižgite mikrofon, povejte svoje vprašanje in nato ugasnite mikrofon.

Na Forumu se boste lahko tudi preizkusili v uporabi Sklepnika, ki ga bomo na skupščini uporabili za sprejemanje sklepov. V ta namen imate tukaj povezavo 
".Sklepnik_URL."registracija.php?u=$u

preko katere boste lahko glasovali na poskusnih sklepih na Forumu. Ker povezava omogoča glasovanje v vašem imenu, je ne delite z drugimi osebami.
POZOR: Ta link velja samo za ta Forum. Za skupščino boste dobili drugo povezavo in ta ne bo več delovala.

Podrobnejša navodila za uporabo Zooma in Sklepnika so v priponki.

V kolikor boste imeli težavo z odpiranjem Zoom seje za forum, lahko pokličete na 041 537 822 (Urša Primožič).

Se vidimo na forumu!
Ekipa IT podpore skupščine in forumov";
*/

$phpMailer->Subject = 'Povezava za online glasovanje: ';

//Vsebina maila
$mail_html = "Pozdravljen_a $ime !<br/><br/>Pošiljamo ti povezavo do on-line glasovanje na dogodku $dogodek_row->ime:<br/>
<a href='".Sklepnik_URL."registracija.php?u=$u'>https://bostjan.info/sklepnik/registracija.php?u=$u</a><br/>
<br/>
<b>Povezave ne smeš deliti</b>, saj je unikatna in omogoča glasovanje v tvojem imenu.<br/>
<br/>
Vsi ostali lahko glasovanje v živo spremljajo <a href='".Sklepnik_URL."?dogodek=".$dogodek_row->access_key."'>tukaj</a>.
<br/><br/>
Ekipa sklepnika.";

//Tekstovna verzija
$mail_text = "Pozdravljen_a $ime !\n\nPošiljamo ti povezavo do on-line glasovanje na dogodku $dogodek_row->ime:
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
