<?php
$time_start = strtotime($dogodek->time_start);
$datum = date("j.n.Y", $time_start);
$dates = date("j.n.X");

if($datum == $danes) {
	$zacetek = "ob ".date("H:i", $time_start);
}
else {
	$zacetek = $datum." ob ".date("H:i", $time_start);
}
?>
<div class="section">
    <div class="container is-max-widescreen">
        <h1 id='aktiven-sklep' class="title is-3">Dogodek se še ni začel...</h1>
        <small>Začne se <?php echo $zacetek; ?>.<br/><i>Rana ura, slovenskih fantov grob.</i></small>
        <script>setTimeout("location.reload();", 300*1000);</script>
    </div>
</div>