<?php
include("config.php");

$login_key = mysql_real_escape_string($_GET['u']);

$query = mysql_query("select * from sklepnik_delegati where login_key = '$login_key' ");

if(mysql_num_rows($query) == 1) {
	$row = mysql_fetch_object($query);
	
	//označi registracijo
	mysql_query("update sklepnik_delegati set registriran = 'da' where id = '$row->id'");
	
	header("Location: ./?u=$login_key");
}
else {
	echo("Neveljavno vstopno geslo!");
}
	