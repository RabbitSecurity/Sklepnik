<?php
include("config.php");

$login_key = mysqli_real_escape_string($mysqli, $_GET['u']);

$query = mysqli_query($mysqli, "select * from sklepnik_delegati where login_key = '$login_key' ");

if(mysqli_num_rows($query) == 1) {
	$row = mysqli_fetch_object($query);
	
	//označi registracijo
	mysqli_query($mysqli, "update sklepnik_delegati set registriran = 'da' where id = '$row->id'");
	
	header("Location: ./?u=$login_key");
}
else {
	echo("Neveljavno vstopno geslo!");
}
	