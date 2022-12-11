<?php
include("config.php");

//Avtorizacija
//nastavi tudi $dogodek_row in $dogodek_id
include("login.php");

//Zdaj pa delaj dalje...
$a = $_GET['a'];

if($a == "koncaj-uvoz") {
	
	$poslji_maile = $_POST['poslji-maile'];

	$rand_str = $_POST['datoteka'];
	$upload_name = "tmp/".$rand_str.'.csv';	
	
	//Preberi podatke
	$rows = file($upload_name);
	if(!$rows) die("Napaka pri branju datoteke?");
	
	//izpusti prvo (header vrstico)
	array_shift($rows);
	
	//čez ostale pa pojdi...
	foreach($rows as $row) {
		
		//poskusi z , tab in podpičjem
		$col = str_getcsv(trim($row), ";");
		if(count($col) == 1) $col = str_getcsv(trim($row), ",");
		if(count($col) == 1) $col = str_getcsv(trim($row), "\t");
		
		//preskoči prazne vrstice (oz. napačno parsane)
		if(count($col) == 1) continue;
		
		//preskoči tudi vrstico, kjer sicer stolpci so, ampak so prazni.
		if(strlen(implode("", array_map('trim', $col))) < 1) continue;
	
		//Magic happens v grdi kodi
			
		$ime = mysqli_real_escape_string($mysqli, $col[0]);
		$priimek = mysqli_real_escape_string($mysqli, $col[1]);
		$email = mysqli_real_escape_string($mysqli, $col[2]);
		$funkcija = mysqli_real_escape_string($mysqli, $col[3]);
		
		$rod = mysqli_real_escape_string($mysqli, $col[4]);
		$rod_kratica = mysqli_real_escape_string($mysqli, $col[5]);
		$obmocje = mysqli_real_escape_string($mysqli, $col[6]);
		$obmocje_kratica = mysqli_real_escape_string($mysqli, $col[7]);
		
		$login_key = randomString(50);
		$vote_key = randomString(50);
		
		// ?preveri, da so stringi res unique? (mislm, sej, verjetnost je skoraj 0 oz. 1:10^80...)
		
		//in ga daj v bazo...
		mysqli_query($mysqli, "insert into sklepnik_delegati (ime, priimek, email, rod, rod_kratica, obmocje, obmocje_kratica, funkcija, dogodek_id, registriran, login_key, vote_key) values ('$ime', '$priimek', '$email', '$rod', '$rod_kratica', '$obmocje', '$obmocje_kratica', '$funkcija', '$dogodek_id', 'ne', '$login_key', '$vote_key')");
		
		//Pošlji mail čez proxy (max 5 na sekundo, zato usleep)
		//Proxy uporabljam zato, da nimam še kle not vse mail kode
		//Realno za velik mailov rabiš nek kompliciran sistem počasnega pošiljanja...
		if($poslji_maile == 1) {
			file_get_contents("https://bostjan.info/sklepnik/poslji-mail.php?u=".$login_key."&auth_key=".$dogodek_row->admin_pass_hash);
			usleep(200); //100us + seveda ping time itd.
		}
	}
	
	
	//in končno preusmeri nazaj.
	header("Location: admin.php");
}

//urejanje delegatov čez JS
if($a == "uredi-delegate") {
	
	$data = json_decode($_POST['data'], true);
	
	if($data === false) die("Error #9: Data parse error"); 

	print_r($data);
	
	//seznam stolpcev v enakem zaporedju kot so v tabeli (ne v bazi ampak v user interface-u!)
	$names = array(
		'ime', 'priimek', 'email', 'rod', 'rod_kratica', 'obmocje', 'obmocje_kratica'
	);
	
	//naredi mysql querije in jih izvedi
	//vsaka vrstica je update enega zapisa
	foreach($data as $id => $columns) {

		$cols = array();
		
		$named = array();
		foreach($columns as $col => $value) {
			$cols[] = $names[$col]." = '".mysqli_real_escape_string($mysqli, $value)."'";
			$named[$names[$col]] = $value;
		}
		
		//V query stlači še dogodek_id
		//tako ne more nekdo urejat delegatov drugih dogodkov, ki jim ni admin
		mysqli_query($mysqli, "update sklepnik_delegati set ".implode(", ", $cols)." where id = '$id' and dogodek_id = '$dogodek_id'\n");
	}
	header("Location: admin.php");
}

//briši delegata čez ajax post request
if($a == "brisi-delegata") {
	$id = (int)$_POST['id'];
	
	//ponovno stlačmo not $dogodek_id
	mysqli_query($mysqli, "delete from sklepnik_delegati where id='$id' and dogodek_id = '$dogodek_id' ");
	
	//tako brisanje ne zbriše odgovorov, ki jih je uporabnik že podal.
	//ker pričakujemo brisanje predvsem pred dogodkom, recimo da to (še) ne bo problem
	
	echo("ok");
	
	//Ker je ajax request, ni redirecta.
	die;
}

//Postavi sklep
if($a == "postavi-sklep") {
	$vprasanje = mysqli_real_escape_string($mysqli, $_POST['vprasanje']);
	$opomba = mysqli_real_escape_string($mysqli, $_POST['pojasnilo']);
	
	//Čas računaj v minutah
	$cas = (float)$_POST['cas'];
	
	$time_start = date('Y-m-d H:i:s', time() + 3); //naprej premaknjen cas
	$time_end = date('Y-m-d H:i:s', time() + $cas*60);
	
	//vnesi sklep s prihodnjim časom aka shekan lock sklepa
	//da se ne zgodi podvajanje vnosov, vnesi najprej prihodnji čas, da se vmes izvedejo insert queriji
	//preden sklep vidijo tudi delegati
	mysqli_query($mysqli, "insert into sklepnik_sklepi (vprasanje, pojasnilo, dogodek_id, time_start, time_end) values ('$vprasanje', '$opomba', '$dogodek_id', '$time_start', '$time_end')");
	
	//id novo vnešenega sklepa
	$sklep_id = mysqli_insert_id();
	
	//vnesi vsem prisotnim (ping < 60s nazaj) prazen odgovor.
	$this_time = time();
	$query = mysqli_query($mysqli, "select * from sklepnik_delegati where dogodek_id = '$dogodek_id' order by rod_kratica, ime, priimek");
	
	$queries = array();
	$delegati = array();
	while($row = mysqli_fetch_object($query)) {
		
		//prikaži samo aktivne oz. če so oddali glas
		if($this_time - strtotime($row->zadnji_ping) < 60) {
			$queries[] = "($row->id, $sklep_id, 0)";
			$delegati[] = $row->id;
		}
	}
	
	//izvrši veliki inset query z vsemi vrednostmi
	//in update query za vse upoštevane delegate
	if(count($queries) > 0) {
		mysqli_query($mysqli, "insert into sklepnik_glasovi (delegat_id, sklep_id, odgovor) values ".implode(", ", $queries));
		
		mysqli_query($mysqli, "update sklepnik_delegati set last_sklep_id = '$sklep_id' where id in (".implode(",", $delegati).")");
	}
	
	//popravi čas sklepa na takoj zdaj (unlock sklep)
	$time_start = date('Y-m-d H:i:s', time());
	mysqli_query($mysqli, "update sklepnik_sklepi set time_start = '$time_start' where id = '$sklep_id'");

	
	header("Location: admin.php");
}

//Umakni zadnji sklep
if($a == "umakni-sklep") {
	
	$query = mysqli_query($mysqli, "select * from sklepnik_sklepi where dogodek_id = '$dogodek_id' order by time_end desc limit 1");
	$row = mysqli_fetch_object($query);
	
	//nastavi mu čas zaključka na zdajšnji čas.
	$time_end = date('Y-m-d H:i:s', time());
	mysqli_query($mysqli, "update sklepnik_sklepi set time_end = '$time_end' where id = '$row->id'");
	
	header("Location: admin.php");
}


//posodobi informacije o dogodku
if($a == "dogodek-info") {
	
	print_r($_POST);
	echo("<br/>");
	
	//Preveri, če je čas v OK formatu.
	if(strtotime($_POST['time_start']) === false) die("Neprepoznaven čas začetka?");
	if(strtotime($_POST['time_end']) === false) die("Neprepoznaven čas konca?");
	
	//kateri stolpci so ok in jih poberi iz $_POST
	$stolpci = array('ime', 'time_start', 'time_end', 'sklepcnost_min_delegatov', 'sklepcnost_min_rodov', 'sklepcnost_min_obmocji');
	
	//naredi mysqli_query
	$data = array();
	foreach($stolpci as $stolpec) {
		$data[$stolpec] = $stolpec." = '".mysqli_real_escape_string($mysqli, $_POST[$stolpec])."'";
	}
	
	//Zapiši v bazo...
	$update_stolpci = implode(", ", $data);
	mysqli_query($mysqli, "update sklepnik_dogodki set ".$update_stolpci." where id = '$dogodek_id'");
	
	header("Location: admin.php");
	
}

//nastavitve maila za delegate
if($a == "dogodek-mail") {
}

//Staro
//funkcije za masovni (masivni, whattever) test

/*
if($a == "masivni-test" && date('Y-m-d') == "2021-02-15") {
	$dogodek_id = 2;
	
	$ime = mysqli_real_escape_string($mysqli, $_POST['ime']);
	$priimek = mysqli_real_escape_string($mysqli, $_POST['priimek']);
	$rod_kratica = mysqli_real_escape_string($mysqli, $_POST['rod']);
	
	if(!empty($ime) && !empty($priimek) && !empty($rod_kratica)) {
		$rod = "Testni rodek";
		$funkcija = 'nacelnik';
		
		$login_key = sha1(time().openssl_random_pseudo_bytes(32)).md5($ime.$priimek);
		$vote_key = sha1(time().openssl_random_pseudo_bytes(32).$ime.$priimek);
		
		//in ga daj v bazo...
		mysqli_query($mysqli, "insert into sklepnik_delegati (ime, priimek, rod, rod_kratica, obmocje, obmocje_test, funkcija, dogodek_id, registriran, login_key, vote_key) values ('$ime', '$priimek', '$rod', '$rod_kratica', 'MZT', 'Mestna Zveza Tabornikov', '$funkcija', '$dogodek_id', 'ne', '$login_key', '$vote_key')");
	}
	else {
		echo("Nisi vpisal vsega??");
	}
	
	header("Location: ./?u=$login_key");
}

if($a == "masivni-test-sklep") {
	$vprasanje = mysqli_real_escape_string($mysqli, $_POST['vprasanje']);
	$opomba = mysqli_real_escape_string($mysqli, $_POST['pojasnilo']);
	$cas = (float)$_POST['cas'];
	
	$dogodek_id = 2;
	
	$time_start = date('Y-m-d H:i:s', time());
	$time_end = date('Y-m-d H:i:s', time() + $cas*60);
	
	mysqli_query($mysqli, "insert into sklepnik_sklepi (vprasanje, pojasnilo, dogodek_id, time_start, time_end) values ('$vprasanje', '$opomba', '$dogodek_id', '$time_start', '$time_end')");
	
	header("Location: ./admin.php");
}
*/

die;