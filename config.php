<?php

//Povezava na bazo
mysql_connect('localhost','rimskace_sklepnik','MXMMf2z2ynX4SkN') or die('Database login error');
mysql_select_db('rimskace_sklepnik') or die('Database select error');

//poskrbi, da povezava deluje kot je treba
mysql_query("set names utf8");


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