<?php
include("config.php");

//Prijava delegata oz. display dogodka
$login_key = mysqli_real_escape_string($mysqli, $_GET['u']);

if(!empty($login_key)) {
	$query = mysqli_query($mysqli, "select * from sklepnik_delegati where login_key = '$login_key' ");

	if (mysqli_num_rows($query) == 1) {
		$user_row = mysqli_fetch_object($query);
        $uporabnik_id = $user_row->id;

		$uporabnik = $user_row->ime . " " . $user_row->priimek . ", " . $user_row->rod . " (" . $user_row->obmocje_kratica . ")";
	} else {
		die("Neveljavno vstopno geslo!");
	}

	//Informacije o dogodku
	$passive_user = false;
	
	$query = mysqli_query($mysqli, "select * from sklepnik_dogodki where id = '$user_row->dogodek_id'");
	$dogodek = mysqli_fetch_object($query);
	$dogodek_naslov = $dogodek->ime;
}
//Pasivni uporabnik ki samo spremlja?
else {
	$dogodek_key = mysqli_real_escape_string($mysqli, $_GET['dogodek']);
	
	if(!empty($dogodek_key)) {
		$query = mysqli_query($mysqli, "select * from sklepnik_dogodki where access_key = '$dogodek_key' ");
		
		//Našli smo dogodek.
		if (mysqli_num_rows($query) == 1) {
			$dogodek = mysqli_fetch_object($query);
			
			$passive_user = true;
		} else {
			die("Neznan dogodek.");
		}
	}
	else {
		die("Neznan dogodek ali delegat.");
	}
}
?>

<?php include_once("templates/header.php") ?>

<div id="conn-notification" class="notification notification-toast is-danger is-light is-hidden">
    <b>Povezava s strežnikom je bila prekinjena.</b> <br/>
    <p>Podatki se ne osvežujejo. Preveri svojo internetno povezavo.</p>
</div>

<div class="flex height-minus-navbar">
    <div id="content" class="flex-grow">
        <?php
        //Ali je dogodek trenutno aktiven?
        $now = time();
        if (strtotime($dogodek->time_start) > $now) {
            include_once("templates/pred-dogodkom.php");
        }
        else if (strtotime($dogodek->time_end) < $now) {
            include_once("templates/po-dogodku.php");
        }
        else {
            include_once("templates/med-dogodkom.php");
        }
        ?>
    </div>

    <?php include_once("templates/footer.php") ?>
</div>
