<?php

//Povezava na bazo
mysql_connect('localhost','****','****') or die('Database login error');
mysql_select_db('******') or die('Database select error');

//poskrbi, da povezava deluje kot je treba
mysql_query("set names utf8");


//Povezava do sklepnika
define(Sklepnik_URL, "https://bostjan.info/sklepnik/");


//SMTP podatki
define("Sklepnik_SMTP_Port", 587);
define("Sklepnik_SMTP_Host", '******');
define("Sklepnik_SMTP_Encryption", '****');
define('Sklepnik_SMTP_Username', '**********');
define('Sklepnik_SMTP_Password', '**********');


//Generiranje tokenov (naj ostane skrivnost)s

//Funkcija za generiranje in preverjanje glasovalnega tokena
function makeToken($u, $sklep_id, $odgovor) {
	return sha1("taborniki".$u."smo".$sklep_id."res".$odgovor."zakon");
}

//Funkcija za hashiranje gesla
function passwordHash($pass) {
	return sha1("soljenhash".$pass."zbogomhackerji".strrev($pass));
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