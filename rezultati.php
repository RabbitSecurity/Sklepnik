<?php
include("config.php");

//za beleženje časa izvajanja
$time_start = microtime(true);

//Pridobi informacije o dogodku
$dogodek_key = mysqli_real_escape_string($mysqli, $_GET['dogodek']);

if(!empty($dogodek_key)) {
    $query = mysqli_query($mysqli, "select * from sklepnik_dogodki where access_key = '$dogodek_key' ");

    if (mysqli_num_rows($query) == 1) {
        $dogodek = mysqli_fetch_object($query);

        //naredi seznam delegatov na dogodku (id=>ime, rod...);
        //za kasnejšo uporabo
        $delegati = array();
        $query = mysqli_query($mysqli, "select * from sklepnik_delegati where dogodek_id = '$dogodek->id' order by obmocje_kratica, rod_kratica, ime, priimek");
        while ($row = mysqli_fetch_object($query)) {
            $delegati[$row->id] = $row;
        }
    } else {
        die("Neznan dogodek.");
    }
}
else {
    die("Neznan dogodek.");
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="sl" class="has-navbar-fixed-top">
<head>
    <meta http-equiv="Content-Type" content="text/html charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Sklepnik - Rezultati</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.1/css/bulma.min.css">
    <link rel='stylesheet' href='app.css?<?php echo filemtime("app.css"); ?>'/>
    <link rel='stylesheet' href='rezultati.css?<?php echo filemtime("rezultati.css"); ?>'/>
    <link href="assets/css/all.css" rel="stylesheet">

    <script type="text/javascript" src="js/funkcije.js"></script>
</head>
<body>

<nav class="navbar is-fixed-top is-primary has-background-primary" role="navigation" aria-label="main navigation">
    <div class="navbar-brand">
        <a class="navbar-item">
            <b><?php echo $dogodek->ime; ?></b>
        </a>
        <a role="button" class="navbar-burger" data-target="navMenu" aria-label="menu" aria-expanded="false">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </a>
    </div>

    <div id="navMenu" class="navbar-menu">
        <div class="navbar-start">
            <span class="navbar-item">Rezultati glasovanj</span>
        </div>

        <div class="navbar-end">
        </div>
    </div>
</nav>

<div class="flex height-minus-navbar">
    <div id="content">

        <?php
        $query = mysqli_query($mysqli, "select * from sklepnik_sklepi where dogodek_id = '$dogodek->id' order by time_end");

        $num = 0;
        $barve_glasov = array("#80ff80", "#ff9f80", "#ffd480"); //za, proti, vzdržan
        $glasovi_class = array("", "result-success", "result-error", "result-abstain"); //za, proti, vzdržan
        $glasovi_txt = array("ni glasoval", "ZA", "PROTI", "VZDRŽAN");

        if(mysqli_num_rows($query) > 0) {

            while($row = mysqli_fetch_object($query)) {
                $num++;

                //števci glasov
                $glasovi = array(
                    '0' => 0,  //prazen glas (prisoten, ni glasoval)
                    '1' => 0,  // ZA
                    '2' => 0,  // PROTI
                    '3' => 0   // VZDRŽAN
                );

                //seznam
                $seznam = array();

                $q = mysqli_query($mysqli, "select * from sklepnik_glasovi where sklep_id = '$row->id'");
                while($r = mysqli_fetch_object($q)) {
                    $glasovi[$r->odgovor]++;
                    $seznam[$r->delegat_id] = $r->odgovor;
                }

                $prisotnih = array_sum($glasovi);
                $veljavnih = $glasovi[1] + $glasovi[2] + $glasovi[3];

                //deleži
                $pct1 = round($glasovi[1]/$prisotnih*100, 1);
                $pct2 = round($glasovi[2]/$prisotnih*100, 1);
                $pct3 = round($glasovi[3]/$prisotnih*100, 1);

                if($pct1 > 50) {
                    $izid = 'SPREJET';
                    $izid_class = $glasovi_class[1];
                }
                else {
                    $izid = 'ZAVRNJEN';
                    $izid_class = $glasovi_class[2];
                }


                echo("
                    <div class='results-table'>
                        <div class='results-table-row'>
                            <div class='results-table-spacer primary'>$num. glasovanje: $row->vprasanje</div>
                        </div>
            
                         <div class='results-table-row'>
                             <div class='results-table-h'>Zap. št. glasovanja</div>
                             <div class='results-table-d'>$num</div>
                             <div class='results-table-h'>Izid</div>
                             <div class='results-table-d $izid_class'>$izid ($pct1%)</div>
                         </div>
            
                         <div class='results-table-row'>
                             <div class='results-table-h'>Sklep</div>
                             <div class='results-table-d'>$row->vprasanje</div>
                         </div>
            
                         <div class='results-table-row'>
                             <div class='results-table-h'>Pojasnilo sklepa</div>
                             <div class='results-table-d'>$row->pojasnilo</div>
                         </div>
            
                         <div class='results-table-row'>
                             <div class='results-table-h'>Začetek glasovanja</div>
                             <div class='results-table-d'>$row->time_start</div>
                             <div class='results-table-h'>Konec glasovanja</div>
                             <div class='results-table-d'>$row->time_end</div>
                         </div>
            
                        <div class='results-table-row'>
                            <div class='results-table-spacer'></div>
                        </div>
            
                        <div class='results-table-row'>
                            <div class='results-table-h'></div>
                            <div class='results-table-d text-bold'>ZA</div>
                            <div class='results-table-d text-bold'>PROTI</div>
                            <div class='results-table-d text-bold'>VZDRŽANI</div>
                        </div>
                        <div class='results-table-row'>
                            <div class='results-table-h'>Št. glasov</div>
                            <div class='results-table-d'>$glasovi[1] ($pct1%)</div>
                            <div class='results-table-d'>$glasovi[2] ($pct2%)</div>
                            <div class='results-table-d'>$glasovi[3] ($pct3%)</div>
                        </div>
                        
                        <div class='results-table-row'>
                            <div class='results-table-spacer'></div>
                        </div>
                        
                        <div class='results-table-row'>
                            <div class='results-table-d text-bold'>Ime in priimek</div>
                            <div class='results-table-d text-bold'>Rod</div>
                            <div class='results-table-d text-bold'>Območje</div>
                            <div class='results-table-d text-bold fixed-small'>Glas</div>
                        </div>
                ");

                //pojdi čez delegate in izpiši
                //(logično bi it čez glasove, ampak če greš čez delegate so sortirani kot je treba.)
                foreach($delegati as $id => $delegat) {
                    if(array_key_exists($id, $seznam)) {
                        $glas = $seznam[$id];
                        echo("
                            <div class='results-table-row'>
                                <div class='results-table-d'>$delegat->ime $delegat->priimek</div>
                                <div class='results-table-d'>$delegat->rod ($delegat->rod_kratica)</div>
                                <div class='results-table-d'>$delegat->obmocje ($delegat->obmocje_kratica)</div>
                                <div class='results-table-d fixed-small $glasovi_class[$glas]'>$glasovi_txt[$glas]</div>
                            </div>
                        ");
                    }
                }
                echo("</div>");
            }
        }
        else {
            echo("Trenutno še ni sklepov za prikaz.");
        }
        ?>

         </div>

        <div class="text-center text-small">Seznam se ne osvežuje samodejno, za osvežitev <a href='#' onclick='location.reload();'>ponovno naloži stran</a>.</div>
        <br>
    </div>

    <?php include_once("templates/footer.php") ?>
</div>


</body>
</html>
<?php echo("<!-- Čas izvajanja: ".(microtime(true)-$time_start)." s-->"); ?>