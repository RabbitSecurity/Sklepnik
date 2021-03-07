<?php
include("config.php");

//za beleženje časa izvajanja
$time_start = microtime(true);

//Pridobi informacije o dogodku
$dogodek = mysql_real_escape_string($_GET['dogodek']);

$query = mysql_query("select * from sklepnik_dogodki where access_key = '$dogodek' ");

if(mysql_num_rows($query) == 1) {
	$dogodek_row = mysql_fetch_object($query);
	
	//naredi seznam delegatov na dogodku (id=>ime, rod...);
	//za kasnejšo uporabo
	$delegati = array();
	$query = mysql_query("select * from sklepnik_delegati where dogodek_id = '$dogodek_row->id' order by obmocje_kratica, rod_kratica, ime, priimek");
	while($row = mysql_fetch_object($query)) {
		$delegati[$row->id] = $row;
	}	
}
else {
	die("Neznan dogodek.");
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" /> 
	<title>Sklepnik - Rezultati glasovanj</title>
	
	<link rel='stylesheet' href='admin.css?<?php echo filemtime("admin.css"); ?>'/>
	
	<script type="text/javascript" src="js/funkcije.js"></script>
</head>
<body>

<h1><?php echo $dogodek_row->ime; ?></h1>

<div id='content'>
<h2>Seznam sklepov in izidov:</h2>

<small>Seznam se ne osvežuje samodejno, za osvežitev <a href='#' onclick='location.reload();'>ponovno naloži stran</a>.</small>
<div class="sklepi">
<?php
$query = mysql_query("select * from sklepnik_sklepi where dogodek_id = '$dogodek_row->id' order by time_end");

$num = 1;
$barve_glasov = array("#80ff80", "#ff9f80", "#ffd480"); //za, proti, vzdržan
$glasovi_txt = array("ZA", "PROTI", "VZDRŽAN");

if(mysql_num_rows($query) > 0) {
	
	while($row = mysql_fetch_object($query)) {
		
		//besedilo sklepa
        echo("<div class='sklep'>");
		echo("<h2>$num. $row->vprasanje</h2>");
		$num++;
		
		//rezultati (ja, verjamem da bi se to dal optimizirat, ampak ker ni neka ključna stvar...)
		
		//števci glasov
		$glasovi = array(
			'1' => 0,
			'2' => 0,
			'3' => 0
		);
		
		//seznam
		$seznam = array();
		
		$q = mysql_query("select * from sklepnik_glasovi where sklep_id = '$row->id'");
		while($r = mysql_fetch_object($q)) {
			$glasovi[$r->odgovor]++;
			$seznam[$r->delegat_id] = $r->odgovor;
		}
		
		$vseh = array_sum($glasovi);
		
		//deleži
		$pct1 = round($glasovi[1]/$vseh*100, 1);
		$pct2 = round($glasovi[2]/$vseh*100, 1);
		$pct3 = round($glasovi[3]/$vseh*100, 1);
		
		echo("<div class='sklep-rezultati'>
        <p>Oddanih glasov: $vseh</p>
		<table>
			<tr><td></td><td align='center'>Glasov</td><td align='center'>Delež</td></tr>
			<tr><td align='right'>ZA:</td><td align='center'>".$glasovi[1]."</td><td align='center'>$pct1%</td></tr>
			<tr><td align='right'>PROTI:</td><td align='center'>".$glasovi[2]."</td><td align='center'>$pct2%</td></tr>
			<tr><td align='right'>VZDRŽANI:</td><td align='center'>".$glasovi[3]."</td><td align='center'>$pct3%</td></tr>
		</table>");
		
		echo("<br/>
		<a href='#seznam-$num' class='poimenski-seznam-toggle' onClick='toggleSeznam($num);return false;'>+ Poimenski seznam glasov</a>");
		
		echo("<div class='poimenski-seznam' id='seznam-$num'>
		<table>
		<tr class='header'><td>Ime in priimek</td><td>Rod</td><td>Območje</td><td>Glas</td></tr>");
		
		//pojdi čez delegate in izpiši
		//(logično bi it čez glasove, ampak če greš čez delegate so sortirani kot je treba.)
		foreach($delegati as $id => $delegat) {		
			if(array_key_exists($id, $seznam)) {
				$glas = $seznam[$id];
				echo("<tr><td>$delegat->ime $delegat->priimek</td><td>$delegat->rod ($delegat->rod_kratica)</td><td>$delegat->obmocje ($delegat->obmocje_kratica)</td><td style='background-color:".$barve_glasov[$glas-1].";' align='center'>".$glasovi_txt[$glas-1]."</td></tr>\n");
			}
		}
		
		echo("</table></div></div></div>\n\n");
	}
}
else {
	echo("Trenutno še ni sklepov za prikaz.<br/>Osveži stran po potrebi...");
}
?>

</div>
</div>

</body>
</html>
<?php echo("<!-- Čas izvajanja: ".(microtime(true)-$time_start)." s-->"); ?>