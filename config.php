<?php
error_reporting(E_ALL & ~E_NOTICE);

//Povezava na bazo
$mysqli = mysqli_connect('localhost','root','', 'sklepnik') or die('Database login error');

//poskrbi, da povezava deluje kot je treba
mysqli_query($mysqli, "set names utf8");


//Povezava do sklepnika
define("Sklepnik_URL", "https://bostjan.info/sklepnik/");


//SMTP podatki
define("Sklepnik_SMTP_Port", 587);
define("Sklepnik_SMTP_Host", 'smtp.office365.com');
define("Sklepnik_SMTP_Encryption", 'starttls');
define('Sklepnik_SMTP_Username', '**************');
define('Sklepnik_SMTP_Password', '**************');

//Super kurac

define('stencas_password', '**************');

//Generiranje tokenov (naj ostane skrivnost)

//Funkcija za generiranje in preverjanje glasovalnega tokena
function makeToken($u, $sklep_id, $odgovor) {
    return sha1("goljufal".$u."pa".$sklep_id."ne".$odgovor."boste");
}

//Funkcija za hashiranje gesla
function passwordHash($pass) {
    return sha1($pass."zelodolgstringkiganemorjopogruntat".strrev($pass));
}

//Funkcija za random string
//Generiraj string dolžine $length z znaki $chars
function randomString($length) {
    $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $out = "";
    for($i = 0; $i < $length; $i++) {
        $out .= $chars[mt_rand(0,strlen($chars)-1)];
    }

    return $out;
}