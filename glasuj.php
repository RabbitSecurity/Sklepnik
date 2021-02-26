<?php
include("config.php");

//poslani podatki
$v = mysql_real_escape_string($_POST['v']);
$token = $_POST['token']; //token ne gre v bazo, ni sanitizinga
$sklep_id = (int)$_POST['sklep'];

//Preveri, a smo našli uporabnika?
$query = mysql_query("select * from sklepnik_delegati where vote_key = '$v'");
$user_row = mysql_fetch_object($query);

if(mysql_num_rows($query) > 0) {
	//označi, da je delegat aktiven
	mysql_query("update sklepnik_delegati set zadnjic_aktiven = NOW() where id = '$user_row->id'");
	
	//za kaj je torej glasoval?
	$glas = -1;
	
	//funkcija makeToken je v config.php (ja, vem da to ni "config", sam mal tud je)
	if($token == makeToken($user_row->login_key, $sklep_id, 1)) $glas = 1;
	else if($token == makeToken($user_row->login_key, $sklep_id, 2)) $glas = 2;
	else if($token == makeToken($user_row->login_key, $sklep_id, 3)) $glas = 3;
	
	if($glas == -1) {
		die("Neveljaven glasovalni žeton?");
	}
	else {
		//a je sklep aktiven? (da ne more kdo glasovat naprej in predvsem za nazaj)
		$now = time();
		$q = mysql_query("select * from sklepnik_sklepi where id = '$sklep_id'");
		$r = mysql_fetch_object($q);
		
		if(strtotime($r->time_start) > $now || strtotime($r->time_end) < $now) die("Glasuješ za neaktiven sklep?");
		
		//ok, vse OK.
		//zabeleži glas oz. ga spremeni če je že oddan
		$q = mysql_query("select * from sklepnik_glasovi where delegat_id = '$user_row->id' and sklep_id = '$sklep_id'");
		if(mysql_num_rows($q) > 0) {
			mysql_query("update sklepnik_glasovi set odgovor = '$glas' where delegat_id = '$user_row->id' and sklep_id = '$sklep_id'");
		}
		else {
			mysql_query("insert into sklepnik_glasovi (delegat_id, sklep_id, odgovor) values ('$user_row->id', '$sklep_id', '$glas')");
		}
		
		echo("ok");
	}
}
else {
	//print_r($_POST);
	die("Neveljaven glasovalni ključ.");
}
