<?php
include("config.php");

//Avtorizacija
//nastavi tudi $dogodek_row in $dogodek_id
include("login.php");

//če ni post request, preusmeri na admin.php
if(!$_POST) {
	header("admin.php");
	die;
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" /> 
	<title>Sklepnik - Admin za <?php echo($dogodek_row->ime); ?></title>
	
	<link rel='stylesheet' href='admin.css?<?php echo filemtime("admin.css"); ?>'/>
	
	<script type="text/javascript" src="funkcije.js"></script>
</head>
<body>

<h1><?php echo($dogodek_row->ime); ?></h1>
<small>Sklepnik - Urejanje dogodka</small>

<div id='content'>

<h2>Pregled uvoženih podatkov</h2>
<br/>
<?php
//Poskusi uploadat
$dat = $_FILES['datoteka'];

//prepoznava končnice
$ext = explode(".",$dat['name']);
$ext = strtolower(array_pop($ext));

if($ext == 'csv' || $ext == 'txt') {
	
	//ime začasne upload datoteke
	$rand_str = randomString(20);
	$upload_name = "tmp/".$rand_str.'.csv';
	
	//Upload je uspel.
	if(move_uploaded_file($dat['tmp_name'], $upload_name)) {
		
		
		echo("Podrobno preglej vse podatke, če so šumniki OK, če so rodovi in območja prav zapisana.<br/>V primeru kakršnekoli napake, popravi datoteko in ponovi upload.<br/><small>(ja, vem da to ni user friendly, nekoč bo to boljše.)</small><br/><br/>");
		
		//Preberi podatke
		$rows = file($upload_name);
		
		//izpusti prvo (header vrstico)
		array_shift($rows);
		
		echo("<table>
			<tr class='header'><td>Ime</td><td>Priimek</td><td>Email</td><td>Funkcija</td><td>Rod</td><td>Kratica</td><td>Območje</td><td>Kratica</td></tr>");
		foreach($rows as $row) {
			
			//poskusi s podpičjem, v in tab - nek seperator bo že ok?
			$col = str_getcsv(trim($row), ";");
			if(count($col) == 1) $col = str_getcsv(trim($row), ",");
			if(count($col) == 1) $col = str_getcsv(trim($row), "\t");
			
			//preskoči prazne vrstice (oz. napačno parsane)
			if(count($col) == 1) continue;
			
			//preskoči tudi vrstico, kjer sicer stolpci so, ampak so prazni.
			if(strlen(implode("", array_map('trim', $col))) < 1) continue;
			
			echo("<tr>");
			foreach($col as $c) echo("<td>$c</td>");
			echo("</tr>\n");
		}
		echo("</table><br/><br/>");
		
		echo("<form method='post' action='shrani.php?a=koncaj-uvoz'>
		<input type='hidden' name='datoteka' value='$rand_str'/>
		
		<!-- še neuporabljeno, za popravljene podatke -->
		<input type='hidden' name='data' value=''/>
		
		<input type='checkbox' value='1' name='poslji-maile'/> Pošlji delegatom vabila na mail<br/>
		<small>Maile lahko posamično pošiljaš tudi kasneje na admin strani.</small><br/>
		<br/>
		
		<input type='submit' value='Uvozi podatke'/>
		</form>");
	}
	//Upload ni uspel (whatever razlog)
	else {
		echo("Napaka pri uploadu!;");
	}
}
else {
	echo("Dovoljene so samo csv datoteke!");
}
?>
</div>


<div id='footer'>
Coded by <a href='https://bostjan.info/'>Boštjan Zajec</a> &copy; 2021<br/>
<small>Prilagojeno za HTML5 brskalnike.</small>
</div>
</body>
</html>