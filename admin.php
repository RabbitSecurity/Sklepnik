<?php
include("config.php");

//Avtorizacija
//nastavi tudi $dogodek_row in $dogodek_id
include("login.php");
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Sklepnik - Admin za <?php echo($dogodek_row->ime); ?></title>

    <link rel='stylesheet' href='admin.css?<?php echo filemtime("admin.css"); ?>'/>

    <script type="text/javascript" src="js/funkcije.js"></script>
    <script type="text/javascript" src="js/admin.js"></script>
</head>
<body>

<h1><?php echo($dogodek_row->ime); ?></h1>
<small>Sklepnik - Urejanje dogodka</small>

<div id='content-admin'>

    <h2>Hitre informacje:</h2>
    <ul>
        <li><a href='?dogodek=<?php echo $dogodek_row->access_key; ?>'>Link za sprotno spremljanje
                rezultatov</a> (brez glasovanja)
        </li>
        <li><a href='rezultati.php?dogodek=<?php echo $dogodek_row->access_key; ?>'>Seznam že postavljenih sklepov in
                rezultatov</a></li>
    </ul>

    Povezave za glasovanje za posamezne uporabnike so na voljo v oddelku "Seznam delegatov".

    <br/><br/>
    <h2>Daj nov sklep na glasovanje:</h2>
    <br/>

    <form action='shrani.php?a=postavi-sklep' method='post'>

        Besedilo sklepa: <input type='text' name='vprasanje' value=''/><br/>
        Pojasnilo: <input type='text' name='pojasnilo' value=''/><br/>
        <small>(prikazno tako kot tale tekst tuki, opcijsko.)</small>

        <br/><br/>
        Čas za glasovanje: <input type='text' name='cas' value='3' size='4' class='inputCenter'/> minute.<br/>
        <br/>
        Sklep se avtomatično pojavi <b>takoj</b> ko klikneš postavi sklep.<br/>
        Sklep izgine po nastavljenem času, lahko pa z gumbom spodaj glasovanje prekineš tudi predčasno.<br/>
        Rezultate seveda lahko gledaš tudi naknadno <a
                href='rezultati.php?dogodek=<?php echo $dogodek_row->access_key; ?>'>tukaj</a>
        <br/><br/>
        <input type='submit' name='power' value='Postavi skep na glasovanje!'/>
    </form>

    <br/>
    <br/>
    <h2>Umakni trenutni sklep:</h2>

    Umik sklepa ne zbriše rezultatov glasovanja temveč le onemogoči nadaljnje glasovanje.
    <br/>
    <br/>
    <form action='shrani.php?a=umakni-sklep' method='post'>
        <input type='submit' name='power' value='Umakni trenutni sklep'/>
    </form>

    <br/><br/>
    <h2>Spremeni informacije o dogodku:</h2>

    <form action='shrani.php?a=dogodek-info' method='post'>

        Ime dogodka: <input type='text' name='ime' value='<?php echo $dogodek_row->ime; ?>'/><br/>
        <br>
        Datum in čas začetka: <input type='text' name='time_start'
                                     value='<?php echo $dogodek_row->time_start; ?>'/><br/>
        Datum in čas konca: <input type='text' name='time_end' value='<?php echo $dogodek_row->time_end; ?>'/><br/>
        <small>Delegati bodo pred začetkom ali po koncu dobili ustrezno obvestilo.</small>

        <br/><br/>
        <small>Pogoji za sklepčnost:</small><br/>
        Št. delegatov: <input type='text' name='sklepcnost_min_delegatov' class='inputCenter' size='4'
                              value='<?php echo $dogodek_row->sklepcnost_min_delegatov; ?>'/><br/>
        Št. rodov: <input type='text' name='sklepcnost_min_rodov' class='inputCenter' size='4'
                          value='<?php echo $dogodek_row->sklepcnost_min_rodov; ?>'/><br/>
        Št. območji: <input type='text' name='sklepcnost_min_obmocji' class='inputCenter' size='4'
                            value='<?php echo $dogodek_row->sklepcnost_min_obmocji; ?>'/><br/>
        <small>V kolikor št. ni pomembno za sklepčnost, vnesi 0 (ničlo).</small><br/>
        <br/>
        <input type='submit' name='power' value='Shrani podatke'/>
    </form>

    <br/><br/>
    <h2>Seznam prejšnjih sklepov:</h2>

    <a href='rezultati.php?dogodek=<?php echo $dogodek_row->access_key; ?>'>Povezava na rezultate</a>

    <br/><br/>
    <h2>Seznam delegatov:</h2>
    <br/>

    <?php

    $query = mysql_query("select * from sklepnik_delegati where dogodek_id = '$dogodek_id' order by rod_kratica, ime, priimek");

    //Delegati so v bazi.
    if (mysql_num_rows($query) > 0) {

        echo("<a href='#seznam-1' onClick='toggleSeznam(1);return false;'>+ Odpri seznam</a>");

        echo("<div class='poimenski-seznam' id='seznam-1'>
	Za urejanje podatka dvoklikni na celico (ne dela na mobitelu). <b>Po spremembi ne pozabi klikniti gumba Shrani!</b>
	<br/><br/>
	Registriran kandidat je kandidat, ki je vsaj 1x odprl povezavo, zato vemo, da nima težav s prijavo.<br/>
	Če kateri od kandidatov nima povezave, mu lahko ponovno pošlješ mail (📧) ali mu kako drugače skopiraš link (klikni 🔗 in potem Ctrl+V kamorkoli že).
	<br/>
	<br/>
	<table id='tabela-delegatov'>
			<tr class='header'><td>Ime</td><td>Priimek</td><td>Email</td><td>Rod</td><td>Kratica</td><td>Območje</td><td>Kratica</td><td>Registriran</td><td colspan='3'>Orodja</td></tr>");

        while ($row = mysql_fetch_object($query)) {
            //Stolpci iz baze
            echo("<tr id='delegat-$row->id'><td>$row->ime</td><td>$row->priimek</td><td>$row->email</td><td>$row->rod</td><td>$row->rod_kratica</td><td>$row->obmocje</td><td>$row->obmocje_kratica</td>
		<td align='center'>$row->registriran</td>");

            //In potem kontrolni gumbi...
            echo("<td class='button-td' title='Kopiraj povezavo za glasovanje v odložišče' onClick=\"kopirajLink(event, $row->id, '$row->login_key')\">🔗</td>
		<td class='button-td' title='(Ponovno) pošlji email s povezavo' onClick=\"posljiMail($row->id, '$row->login_key')\">📧</td>
		<td class='button-td' title='Odstrani delegata' onClick=\"brisiDelegata($row->id, '$row->ime $row->priimek')\">❌</td>
		</tr>\n");
        }

        echo("</table>");

        //in še JS edit tabele
        echo("<!-- JS edit tabele -->
	<script type='text/javascript'>ediTable('tabela-delegatov', [0,1,2,3,4,5,6]);</script>
	
	<br/>
	<!-- Pošlji spremembe -->
	<form action='shrani.php?a=uredi-delegate' method='post' id='the-form'>
	<input type='hidden' value='' name='data' id='data-input'/>
	<input type='button' value='Shrani podatke' onclick='saveData();'/>
	</form>
	");

        echo("</div>");
    } else {
        echo("Ni vnešenih delegatov.");
    }
    ?>

    <!-- za prikaz povezave delegata -->
    <div id='povezava'
         style='display:none;position:absolute;padding:10px;border:1px solid silver;background-color:#fff;'>
        <input id='povezava-link' value='' style='width:300px;'/>
        &nbsp;&nbsp;<span class='button-td' onclick='skrijPovezavo();'>❌</span>
    </div>


    <br/><br/>
    <h2>Dodaj/uvozi nove delegate</h2>

    <form action='admin-uvozi.php' method='post' enctype='multipart/form-data'>
        Datoteka s podatki (.csv):<br/>
        <input type='file' name='datoteka'/>
        <br/>
        <br/>
        Format podatkov: Ime, Priimek, email, funkcija, rod, kratica rodu, območje, kratica območja (ločeno z vejico ali
        tab, en na vrstico).<br/>
        Prva vrstica v datoteki(naslovna, z imeni stolpcev) bo izpuščena.<br/>
        <br/>
        Prenesi vzorčno datoteko <a href='delegati-tpl.xlsx'>tukaj</a>, uredi podatke in nato shrani kot .csv (CSV
        UTF-8).
        <br/><br/>
        Po uploadu datoteke sledi pred pošiljanjem vabil še en korak preverjanja, če so podatki OK.

        <br/><br/>
        <input type='submit' name='power' value='Uvozi podatke'/>
    </form>

</div>


<div id='footer'>
    Coded by <a href='https://bostjan.info/'>Boštjan Zajec</a> &copy; 2021<br/>
    <small>Prilagojeno za HTML5 brskalnike.</small>
</div>
</body>
</html>