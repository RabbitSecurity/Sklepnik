<?php
//25+60+45+25+220+100+40+30+40

include("config.php");

//Pridobi informacije o dogodku
$dogodek_key = mysql_real_escape_string($_GET['dogodek']);

$query = mysql_query("select * from sklepnik_dogodki where access_key = '$dogodek_key' ");

if(mysql_num_rows($query) == 1) {
	$dogodek = mysql_fetch_object($query);
	
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
	<title>Sklepnik - Pregled dogodka</title>
	
	<link rel='stylesheet' href='style.css?<?php echo filemtime("style.css"); ?>'/>
	
	<script type="text/javascript" src="js/funkcije.js"></script>
	<script type="text/javascript" src="js/sklepnik.js?<?php echo filemtime("sklepnik.js"); ?>"></script>
</head>
<body>

<h1>Sklepnik - <?php echo $dogodek->ime; ?></h1>
<small>ZTS glasuje online</small>

<div id='content'>

<?php
//Ali je dogodek trenutno aktiven?
$now = time();
if(strtotime($dogodek->time_start) > $now) {
	echo("<h2>Dogodek se še ni začel...</h2>
	<small>Rana ura, slovenskih fantov grob.</small>");
}
//Dogodka je že konec
else if(strtotime($dogodek->time_end) < $now) {
	echo("<h2>Dogodka je že konec :(</h2>
	<small>Več sreče prihodnjič.</small>
	<br/><br/>
	Klikni <a href='rezultati.php?dogodek=".$dogodek->access_key."'>tukaj</a> za ogled rezultatov glasovanja.</a>");
}
//Dogodek je aktiven! :)
else {
?>
<h3>Pregled trenutnega glasovanja.</h3>
<small><span id='status' style='font-weight:bold;'>?</span></small>

<br/>
<br/>
<h2 style='color:gray;'>Trenutno na glasovanju:</h2>
<div id='glasovalnik'>

<h1 id='aktiven-sklep'>Trenutno ni aktivnega glasovanja.</h1>
<small id='pojasnilo'></small>

<div id='trenuten-potek' style='display:none;'>
	<h2>Trenuten potek glasovanja:</h2>

	Oddanih glasov: <span id='glasov'>?</span> (<span id='udelezba'>?</span>% prisotnih)<br/>
	<br/>
	<table>
		<tr><td></td><td align='center'>Glasov</td><td align='center'>Delež</td></tr>
		<tr><td align='right'>ZA:</td><td id='glasovi-za' align='center'></td><td align='center'></td></tr>
		<tr><td align='right'>PROTI:</td><td id='glasovi-proti' align='center'></td><td align='center'></td></tr>
		<tr><td align='right'>VZDRŽANI:</td><td id='glasovi-vzdrzani' align='center'></td><td align='center'></td></tr>
	</table>
</div>

</div>

<br/>
<?php
echo("Klikni <a href='rezultati.php?dogodek=".md5($user_row->dogodek_id)."' target='_new'>tukaj</a> za rezultate prejšnjih glasovanj in poimenski seznam.");
?>
<br/>
<br/>
<br/>
<br/>

<div id='sklepcnost'>
<div id='sklepcnost-div'>Sklepčnost ni znana</div>
</div>

<h2>Prisotni delegati <span id='st-delegatov'></span>:</h2>

<div id='prisotni-delegati'>
</div>
<br/>
Barva delegatov se spreminja glede na njihov aktualen glas:
<span style='background-color:#80ff80'>za</span>,
<span style='background-color:#ff9f80'>proti</span> in 
<span style='background-color:#ffd480'>vzdržan</span>.<br/>
<span style='background-color:#eee'>Siva</span> barva pomeni, da delegat ni oddal glasu ali pa ni aktivnega sklepa.


<br/><br/>
<h2>Prisotni rodovi (<span id='st-rodov'>?</span>):</h2>

<ul id='rodovi'>
</ul>

<br/>
<h2>Prisotna območja (<span id='st-obmocji'>?</span>):</h2>

<ul id='obmocja'>
</ul>

</div>
<script type="text/javascript">
//glasovalni ključi uporabnika
var passive_user = true;
var login_key = "<?php echo("!".$dogodek_key); ?>";

//pogoji za sklepčnost
var pogoji_sklepcnost = [<?php echo("$dogodek->sklepcnost_min_delegatov,$dogodek->sklepcnost_min_rodov,$dogodek->sklepcnost_min_obmocji"); ?>];

//seznam delegatov (da je manj podatkov ob pinganju)
<?php
$query = mysql_query("select * from sklepnik_delegati where dogodek_id = '$dogodek_id'");
$delegati = array();
while($user_row = mysql_fetch_object($query)) {
	$delegati[$user_row->id] = array_map('htmlspecialchars', array(
		"$user_row->ime $user_row->priimek",
		$user_row->rod_kratica,
		$user_row->rod,
		$user_row->obmocje_kratica,
		$user_row->obmocje
	));
}

echo("delegati_index = ".json_encode($delegati));
?>
</script>

<?php
//konec if stavka za "ali je dogodek aktiven"
}
?>

<div id='footer'>
Coded by <a href='https://bostjan.info/'>Boštjan Zajec</a> &copy; 2021<br/>
<small>Prilagojeno za HTML5 brskalnike.</small>
</div>

</body>
</html>