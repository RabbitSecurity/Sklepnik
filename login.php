<?php
//zahteva MySQL povezavo s serverjem preko config.php

//Avtorizacija uporabnika prek HTTP autentikacije
$uporabnik = mysql_real_escape_string(urldecode($_SERVER['PHP_AUTH_USER']));
$hash = passwordHash($_SERVER['PHP_AUTH_PW']);

$query = mysql_query("select * from sklepnik_dogodki where admin_username = '$uporabnik' and admin_pass_hash = '$hash' ");

//prijava ni uspela
if(mysql_num_rows($query) != 1) {
	header('WWW-Authenticate: Basic realm="administracija"');
	header('HTTP/1.0 401 Unauthorized');

	exit("Sori, tuki je neki tazga, kar ni za vsazga :)");
}
else {
	$dogodek_row = mysql_fetch_object($query);
	$dogodek_id = $dogodek_row->id;
}
