<?php
include("config.php");

$a = $_GET['a'];

//Je login ključ podan?
$u = mysqli_real_escape_string($mysqli, $_GET['u']);
if(empty($u)) die("false\nfalse\nNeznan vstopni ključ.");


//Tukaj potem loči, a gre za delegata, ali samo za nekoga ki spremlja volitve pasivno
//če je uporabnik pasiven (ne more glasovat), se njegov "u" začne s ! in pomeni access_key dogodka
if($u[0] != "!") {
	$pasiven_uporabnik = false;
	$query = mysqli_query($mysqli, "select * from sklepnik_delegati where login_key = '$u'");
	if(mysqli_num_rows($query) != 1) die("false\nfalse\nNeveljaven vstopni ključ?");
	
	//očitno uporabnik obstaja...
	$user_row = mysqli_fetch_object($query);
	$dogodek_id = $user_row->dogodek_id;
}
//samo nekdo spremlja volitve v živo...
//to dela samo na a=ping
else {
	$pasiven_uporabnik = true;
	$user_row = null;
	
	//preveri, če dogodek obstaja
	$dogodek_key = substr($u, 1, strlen($u)-1);
	
	$query = mysqli_query($mysqli, "select * from sklepnik_dogodki where access_key = '$dogodek_key'");
	if(mysqli_num_rows($query) != 1) die("false\nfalse\nNeveljaven id dogodka?");
	
	//če ja, ga zabeleži za naprej.
	$dogodek_row = mysqli_fetch_object($query);
	$dogodek_id = $dogodek_row->id;
}



//Periodični ping z vsemi informacijami
if($a == "ping") {
	$aktiven = $_GET['aktiven'];
	
	$now = date("Y-m-d H:i:s");
	
	
	echo("ok\n");
	
	//kateri dogodek je to vemo iz user_row->dogodek_id

	//id naslednjega/aktivnega sklepa:
	$query = mysqli_query($mysqli, "select * from sklepnik_sklepi where dogodek_id = '$dogodek_id' and time_start < NOW() order by time_end desc limit 1");

	$aktiven_sklep = -1;
	if(mysqli_num_rows($query) > 0) {
		$row = mysqli_fetch_object($query);

		//prikaži sklep samo, če je še aktiven
		if(strtotime($row->time_end) > time()) {
			echo("$row->id\n");
			$aktiven_sklep = $row->id;
		}

		//sicer vrni, ni sklepa.
		else echo("-1\n");
	}
	else {
		echo("-1\n");
	}
	
	//označi ping in zadnji sklep pri katerem je sodeloval
	//vrini tudi update zadnjega sklepa, če ta še ni zapisan.
	if(!$pasiven_uporabnik) {
		
		//vnesi prazen glas, če še ni oddan
		$zadnji_sklep_sql = "";
		if($aktiven_sklep > 0 && $user_row->last_sklep_id < $aktiven_sklep) {
			
			//dodaj update tega stolpca v user tabeli
			$zadnji_sklep_sql = ", last_sklep_id = '$aktiven_sklep' ";
			
			//vnesi prazen glas v tabelo odgovorov
			mysqli_query($mysqli, "insert into sklepnik_glasovi (delegat_id, sklep_id, odgovor) values ('$user_row->id', '$aktiven_sklep', '0')");
		}
	
		if($aktiven == "da") {
			mysqli_query($mysqli, "update sklepnik_delegati set zadnji_ping = '$now', zadnjic_aktiven = '$now' $zadnji_sklep_sql where id = '$user_row->id'");
		}
		else {
			mysqli_query($mysqli, "update sklepnik_delegati set zadnji_ping = '$now' $zadnji_sklep_sql where id = '$user_row->id'");
		}
	}
	
	
	//če je sklep aktiven, pokaži tudi rezultate glasovanja
	$oddani_glasovi = array();
	if($aktiven_sklep > 0) {
		$query = mysqli_query($mysqli, "select * from sklepnik_glasovi where sklep_id = '$aktiven_sklep'");
		while($row = mysqli_fetch_object($query)) {
			$oddani_glasovi[$row->delegat_id] = $row->odgovor;
		}
	}
	
	//seznam aktivnih delegatov/glasovalcev:
	$this_time = time();
	$query = mysqli_query($mysqli, "select * from sklepnik_delegati where dogodek_id = '$dogodek_id' order by rod_kratica, ime, priimek");
	
	$aktivni = array();
	
	//rodov in območji ne pošilja več, ker je to že v indeksu pri userju.
	//$rodovi = array();
	//$obmocja = array();
	
	while($row = mysqli_fetch_object($query)) {
		
		//prikaži uporabnikov zadnji glas, če obstaja
		if($aktiven_sklep > 0) {
			if(array_key_exists($row->id, $oddani_glasovi)) $glas = $oddani_glasovi[$row->id];
			else $glas = -1;
			
		}
		else $glas = -1;
		
		//prikaži samo aktivne oz. če so oddali glas
		if($this_time - strtotime($row->zadnji_ping) < 60 || array_key_exists($row->id, $oddani_glasovi)) {
			
			$aktivni[] = array((int)$row->id, (int)$glas, (int)$row->rod_id);
		
			//$rod = "$row->rod ($row->rod_kratica)";
			//if(!in_array($rod, $rodovi)) $rodovi[] = $rod;
		}
	}
	
	echo json_encode($aktivni)."\n";
	
	//in potem še seznam rodov -> tega ne pošiljamo več ker je vse user-cached
	//echo json_encode($rodovi)."\n";
	
	
	//pošlji v zadnji vrstici še morebiten JS ukaz
	// 0 = nič, r = reload...
	echo("0");
}

if($a == "naslednji-sklep") {
	$u = mysqli_real_escape_string($mysqli, $_GET['u']);
	
	//kateri dogodek je to že vemo.
	echo("ok\n");
	
	//za vsak slučaj select čez več sklepov, morda jih je več aktivnih po urah.
	//v vsakem primeru izberi samo zadnjega (break v while)
	$query = mysqli_query($mysqli, "select * from sklepnik_sklepi where dogodek_id = '$dogodek_id' and time_start < NOW() order by time_end desc limit 10");
	
	while($row = mysqli_fetch_object($query)) {
	
		//prikaži sklep samo, če je še aktiven
		$now = time();
		$time_left = strtotime($row->time_end) - $now;
		if(strtotime($row->time_start) < $now && $time_left > 0) {
			
			//naredi glasovalne tokene
			//če gre za delegata in ne pasivnega spremljevalca...
			if(!$pasiven_uporabnik) {
				$token1 = makeToken($u, $row->id, 1); //ZA
				$token2 = makeToken($u, $row->id, 2); //PROTI
				$token3 = makeToken($u, $row->id, 3); //VZDRŽAN
			}
			else {
				$token1 = -1;
				$token2 = -1;
				$token3 = -1;
			}
			
			echo json_encode(
				array(
					$row->id,
					$row->vprasanje,
					$row->pojasnilo,
					$time_left,
					array($token1, $token2, $token3)
				)
			);
		}
		
		//sicer vrni, ni sklepa.
		else echo("false");
		
		//samo en sklep je lahko aktiven naenkrat.
		break;
	}
}

//vrni seznam delegatov (enako kot v index.php)
if($a == "delegati") {
	$query = mysqli_query($mysqli, "select * from sklepnik_delegati where dogodek_id = '$dogodek_id'");

	$delegati = array();
	while($row = mysqli_fetch_object($query)) {
		$delegati[$row->id] = array_map('htmlspecialchars', array(
			"$row->ime $row->priimek",
			$row->rod_kratica,
			$row->rod,
			$row->obmocje_kratica,
			$row->obmocje
		));
	}

	echo json_encode($delegati);
}