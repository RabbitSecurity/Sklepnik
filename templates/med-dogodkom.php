<div class="section">
    <div class="container is-max-widescreen">

        <p class="has-text-grey">Trenutno na glasovanju:</p>
        <div id='glasovalnik'>

            <h1 id='aktiven-sklep' class="title is-3">Trenutno ni aktivnega glasovanja.</h1>
            <h2 id='pojasnilo' class="subtitle is-5"></h2>

            <div id='odgovori' style='display:none;'>
                <input type='button' value='ZA' class='button is-success' onclick='glasuj(1)' id='glas-1'/>
                <input type='button' value='PROTI' class='button is-danger' onclick='glasuj(2)' id='glas-2'/>
                <input type='button' value='VZDRŽAN' class='button is-info' onclick='glasuj(3)' id='glas-3'/>

                <p class="has-text-grey is-size-7">Glas lahko spremeniš dokler poteka glasovanje o sklepu.</p>
            </div>

            <div id='hvala' style='display:none;'>
                <h2 class="title is-5">Hvala za tvoj glas!</h2>
            </div>

            <br/>

            <div id='trenuten-potek' style='display:none;'>
                <p class="has-text-grey">Rezultati trenutnega glasovanja:</p>
                <div class="table-container">
                    <table class="table is-bordered rezultati-tabela">
                        <tr>
                            <th>ZA</th>
                            <th>PROTI</th>
                            <th>VZDRŽANI</th>
                        </tr>
                        <tr>
                            <td id='glasovi-za'></td>
                            <td id='glasovi-proti'></td>
                            <td id='glasovi-vzdrzani'></td>
                        </tr>
                    </table>
                </div>

                <div id="sklep-sprejet" class="is-success notification is-hidden">
                    Sklep je <b>podprlo</b> več kot 50% navzočih.
                </div>
                <div id="sklep-zavrnjen" class="is-danger notification is-hidden">
                    Sklep je <b>zavrnilo</b> več kot 50% navzočih.
                </div>

                <p class="">Št. oddanih glasov: <b id='glasov'>?</b> (<span id='udelezba'>?</span>% prisotnih)</p>
            </div>

        </div>

    </div>
</div>

<div class="section">
    <div class="container is-max-widescreen">
        <h2>Prisotni delegati <span id='st-delegatov'></span>:</h2>
        <div id='prisotni-delegati'></div>

        <br/><br/>
        <h2>Prisotni rodovi (<span id='st-rodov'>?</span>):</h2>
        <ul id='rodovi'></ul>

        <br/>
        <h2>Prisotna območja (<span id='st-obmocji'>?</span>):</h2>
        <ul id='obmocja'></ul>
    </div>
</div>

<script type="text/javascript">
    //glasovalni ključi uporabnika
    <?php
    if(!$passive_user) {
        echo("var passive_user = false;
		var login_key = '".$user_row->login_key."';
		var vote_key = '".$user_row->vote_key."';");
    }
    else {
        echo("var passive_user = true;
		var login_key = '!".$dogodek->access_key."';
		var vote_key = '';");
    }
    ?>


    //pogoji za sklepčnost
    var pogoji_sklepcnost = [<?php echo("$dogodek->sklepcnost_min_delegatov,$dogodek->sklepcnost_min_rodov,$dogodek->sklepcnost_min_obmocji"); ?>];

    //seznam delegatov (da je manj podatkov ob pinganju)
    <?php
        $query = mysqli_query($mysqli, "select * from sklepnik_delegati where dogodek_id = '$user_row->dogodek_id'");
        $delegati = array();
        while ($user_row = mysqli_fetch_object($query)) {
            $delegati[$user_row->id] = array_map('htmlspecialchars', array(
                "$user_row->ime $user_row->priimek",
                $user_row->rod_kratica,
                $user_row->rod,
                $user_row->obmocje_kratica,
                $user_row->obmocje
            ));
        }

        echo("delegati_index = " . json_encode($delegati));
        ?>;
    //id delegata z odprto sejo damo v JS spremenljivko
    const trenutni_delegat = <?php echo (!$passive_user) ? $uporabnik_id : -1; ?>;

    //vklopi pinger
    window.onload = pinger;
</script>
