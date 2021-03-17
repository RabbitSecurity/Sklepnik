

//Nastavljivi parametri
var barve_glas = ['success', 'danger', 'info'];
var ping_interval = 3000; //interval pinganja za nove sklepe/update rezultatov


//Tekoči parametri
var zadnji_sklep = "-1";

var glas_aktiven = false;
var zadnji_glas = -1;

var first_ping = false;
var last_response = 0;
var page_load_time = new Date().getTime();

var last_ping_time = page_load_time;
var connection_errors = 0;

//Glasovalni žetoni za aktualen sklep
var vote_tokens = [];

function pinger() {

	var aktiven = "ne";

	//če to nekdo spremlja (npr na zoomu prek shared screen), lahko dobi tako informacije hitreje.
	if(passive_user) ping_interval = 3000;
	
	//link s superhitrim osveževanjem
	if(document.location.toString().indexOf('&hitreje!') > -1) {
		ping_interval = 500;
	}

	ajax.get("ping.php?a=ping&u=" + login_key + "&aktiven=" + aktiven, pingResponse)
	setTimeout("pinger()", ping_interval);
	
	var now = new Date().getTime();
	
	//štej št. napak v povezavi
	//če je prvi ping po dolgem času mu daj malo miru (da na telefonu ni lažnih alarmov)
	if(now - last_ping_time > ping_interval*5) {
		connection_errors = 0;
	}
	
	if(first_ping) {
		//prikaži aktivnost povezave (10s)
		if(now - last_response < 10000) {
			//povezava je OK
			showConnectionStatus(0)
		}
		else {
			//povezava prekinjena
			connection_errors++;
			
			//če ni prvi ping po dolgem času
			if(connection_errors > 3) {
				showConnectionStatus(1);
			}
		}
	}
	else {
		showConnectionStatus(-1);
	}
	
	last_ping_time = now;
}

//Premaknjeno na page, da se zažene samo če je dogodek aktiven
//window.onload = pinger;

// -1 = loading
//  0 = connection ok
//  1 = connection error
function showConnectionStatus(status) {
	if(status > 0) {
		$('conn-status-error').classList.remove('is-hidden');
		$('conn-status-ok').classList.add('is-hidden');
		$('conn-status-wait').classList.add('is-hidden');
		$('conn-notification').classList.remove('is-hidden');
	}
	else if (status < 0) {
		$('conn-status-error').classList.add('is-hidden');
		$('conn-status-ok').classList.add('is-hidden');
		$('conn-status-wait').classList.remove('is-hidden');
		$('conn-notification').classList.add('is-hidden');
	} else {
		$('conn-status-error').classList.add('is-hidden');
		$('conn-status-ok').classList.remove('is-hidden');
		$('conn-status-wait').classList.add('is-hidden');
		$('conn-notification').classList.add('is-hidden');
	}
}


function pingResponse(txt) {
	first_ping = true;
	
	var lines = txt.split("\n");
	if(lines[0] == "ok") {
		
		//zabeleži in prikaži da je povezava ok
		last_response = new Date().getTime();
		connection_errors = 0;
		showConnectionStatus(0);
		
		//a je na voljo za glasovanje že kak nov sklep?
		if(zadnji_sklep != lines[1]) {
			ajax.get("ping.php?a=naslednji-sklep&u=" + login_key, prikaziSklep);
		}
		
		//prikaži aktivne delegate:
		var delegati = JSON.parse(lines[2]);


		$('st-delegatov').innerHTML = '(' + delegati.length + ')';
		
		var html = "";
		var barva = "";
		
		//preštej glasove, rodove, območja
		var glasovi = [0, 0, 0];
		var rodovi = [];
		var obmocja = [];
		
		//a je treba updatat delegate?
		var update_delegate = false;
		
		for(var i = 0; i < delegati.length; i++) {

			//posodobimo gumbe glede na stanje iz baze
			if(delegati[i][0] == trenutni_delegat) {
				if (delegati[i][1] > 0) {
					glasuj(delegati[i][1],0);
				} else {
					pocisti_gumbe();
				}
			}

			barva = "grey";
			if(delegati[i][1] > 0) {
				barva = barve_glas[delegati[i][1] - 1];
				glasovi[delegati[i][1] - 1]++;
			}
			
			var delegat_id = delegati[i][0];
			
			//delegat je v indeksu
			if(typeof(delegati_index[delegat_id]) == "object") {
				var delegat = delegati_index[delegat_id][0] + " (" + delegati_index[delegat_id][1] + ")";
				html += `<div class="has-background-${barva}">${delegat}</div>`;
				
				//rod v obliki Ime (kratica)
				var rod_kratica = delegati_index[delegat_id][1];
				
				//Ignoriraj rod "ZTS" ker ne štejejo v sklepčnost
				var rod = delegati_index[delegat_id][2] + " (" + rod_kratica + ")";
				if(rod_kratica != "ZTS" && rodovi.indexOf(rod) < 0) rodovi.push(rod);
				
				//obmocje v obliki Ime (kratica)
				var obmocje_kratica = delegati_index[delegat_id][3];
				
				//Ignoriraj območje "ZTS" ker ne štejejo v sklepčnost
				var obmocje = delegati_index[delegat_id][4] + " (" + obmocje_kratica + ")";
				if(obmocje_kratica != "ZTS" && obmocja.indexOf(obmocje) < 0) obmocja.push(obmocje);
			}
			//delegata ni v indeksu?
			//-> lahko da je bil kdo dodan naknadno
			else {
				update_delegate = true;
				console.log('Nov delegat?');
			}
		}
		
		$('prisotni-delegati').innerHTML = html;
		
		var vsota = glasovi[0]+glasovi[1]+glasovi[2];
		if(vsota > 0) {
			//prikaži aktivne rezultate
			$('sklep-sprejet').classList.add('is-hidden');
			$('sklep-zavrnjen').classList.add('is-hidden');
			
			$('glasov').innerHTML = vsota;
			
			$('glasovi-za').innerHTML = `${glasovi[0]} (${pct(glasovi[0]/vsota)} %)`;
			$('glasovi-proti').innerHTML = `${glasovi[1]} (${pct(glasovi[1]/vsota)} %)`;
			$('glasovi-vzdrzani').innerHTML = `${glasovi[2]} (${pct(glasovi[2]/vsota)} %)`;
			
			$('udelezba').innerHTML = pct(vsota/delegati.length);
			
			$('trenuten-potek').style.display = 'block';

			// ali je sklep sprejet/zavrnjen?
			if ((glasovi[0])/delegati.length > 0.5) {
				$('sklep-sprejet').classList.remove('is-hidden');
			} else if ((glasovi[1])/delegati.length > 0.5) {
				$('sklep-zavrnjen').classList.remove('is-hidden');
			}
			
		}
		else {
			$('trenuten-potek').style.display = 'none';
		}
		
		//prikaži aktivne rodove
		// (rodove smo preštel že prej)
		if(rodovi !== false && rodovi.length > 0) {
			rodovi.sort();
			
			//zbriši prejšn seznam.
			$('rodovi').innerHTML = "";
			
			var html = "";
			for(var i = 0; i < rodovi.length; i++) {
				html += "<li>" + rodovi[i] + "</li>";
			}
			
			//posodobi
			$('rodovi').innerHTML = html;
			$('st-rodov').innerHTML = rodovi.length;
		}
				
		//Prikaži aktivna območja:
		if(obmocja !== false && obmocja.length > 0) {
			obmocja.sort();
			
			//zbriši prejšn seznam.
			$('obmocja').innerHTML = "";
			
			var html = "";
			for(var i = 0; i < obmocja.length; i++) {
				html += "<li>" + obmocja[i] + "</li>";
			}
			
			//posodobi
			$('obmocja').innerHTML = html;
			$('st-obmocji').innerHTML = obmocja.length;
		}
		
		//Prikaži sklepčnost
		if(delegati.length >= pogoji_sklepcnost[0] && rodovi.length >= pogoji_sklepcnost[1] && obmocja.length >= pogoji_sklepcnost[2]) {
			$('quorum-status-error').classList.add('is-hidden');
			$('quorum-status-ok').classList.remove('is-hidden');
			$('quorum-status-wait').classList.add('is-hidden');
		}
		else {
			$('quorum-status-error').classList.remove('is-hidden');
			$('quorum-status-ok').classList.add('is-hidden');
			$('quorum-status-wait').classList.add('is-hidden');
		}
		
		//Update seznama delegatov
		if(update_delegate) {
			ajax.get("ping.php?a=delegati&u=" + login_key, updateDelegatov);
		}
	}
	
	//ukazi iz kontrolnega centra
	//omogočajo forced reload ob ugotovljeni napaki
	if(lines[3] != "0") {
		
		//reload, največ 1x na minuto
		if(lines[3].charAt(0) == 'r' && new Date().getTime()-page_load_time > 60*1000) {
			location.reload();
		}
	}
}

function prikaziSklep(txt) {
	var lines = txt.split("\n");
	if(lines[0] == "ok") {
		var data = JSON.parse(lines[1]);
		
		//objavi naslednji slkep
		if(data !== false) {
			$('aktiven-sklep').innerHTML = data[1];
			$('pojasnilo').innerHTML = data[2];
			
			//shrani id sklepa
			zadnji_sklep = data[0];
			
			if(!passive_user) {
				
				//ponastavi voting gumbe
				glas_aktiven = false;
				
		
				$('odgovori').style.display = 'block';
				
				//shrani voting žetone:
				vote_tokens = data[4];
			}
		}
		//pobriši od prejšnjega sklepa
		else {
			zadnji_sklep = "-1";
			
			$('aktiven-sklep').innerHTML = "Trenutno ni aktivnega glasovanja.";
			$('pojasnilo').innerHTML = "";
			
			if(!passive_user) {
				$('odgovori').style.display = 'none';
			}
		}
	}
}

function glasuj(glas, sendToBackend = 1) {
	pocisti_gumbe();

	$('glas-' + glas).classList.remove('unselected');
	$('glas-' + glas).classList.add('selected');
	
	//ce glasuje user pošlji glas oz. pravi token, lahko uporabimo tudi samo za posodabljanje gumbov
	if ( sendToBackend ) {
		ajax.post("glasuj.php", glasZabelezen, "v=" + vote_key + "&sklep=" + zadnji_sklep + "&token=" + vote_tokens[glas - 1]);
	}
	
	glas_aktiven = true;
	zadnji_glas = glas;

	//aktiviraj glasovalni timer (neaktivno)
	//glas_timer = 10;
	//if(!glas_aktiven) timer();
}

function pocisti_gumbe() {
	$('glas-1').classList.remove('selected');
	$('glas-2').classList.remove('selected');
	$('glas-3').classList.remove('selected');

	$('glas-1').classList.add('unselected');
	$('glas-2').classList.add('unselected');
	$('glas-3').classList.add('unselected');

}

function glasZabelezen(txt) {
	if(txt == "ok") {
	}
	else {
		alert("Napaka pri glasovanju!\n" + txt);
	}
}

//update seznama delegatov iz ajax requesta
function updateDelegatov(txt) {
	var novi_delegati = JSON.parse(txt);
	
	if(novi_delegati !== false) {
		delegati_index = novi_delegati;
	}
}

//prikaži odštevalnik na gumbu
//neuporabljeno
function timer() {
	
	var gumb = $('glas-' + zadnji_glas);
	gumb.value = gumb.value.split(' ')[0] + ' (' + glas_timer + ')';
	
	glas_timer--;
	if(glas_timer > 0) {
		setTimeout(timer, 1000);
	}
	else {
		glas_aktiven = false;
		gumb.value = gumb.value.split(' ')[0];
		$('glas-' + glas).style.backgroundColor = '';
	}
}

function pct(val) {
	return (Math.round(val*100*10)/10);
}