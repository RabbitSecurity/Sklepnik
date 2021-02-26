

//Nastavljivi parametri
var barve_glasu = ["#80ff80", "#ff9f80", "#ffd480"]; //barve ZA, PROTI in VZDRŽAN
var ping_interval = 3000; //interval pinganja za nove sklepe/update rezultatov


//Tekoči parametri
var zadnji_sklep = "-1";

var glas_aktiven = false;
var zadnji_glas = -1;

var first_ping = false;
var last_response = 0;
var page_load_time = new Date().getTime();

//Glasovalni žetoni za aktualen sklep
var vote_tokens = [];


function pinger() {

	var aktiven = "ne";

	//če to nekdo spremlja (npr na zoomu prek shared screen), lahko dobi tako informacije hitreje.
	if(passive_user) ping_interval = 1000;

	ajax.get("ping.php?a=ping&u=" + login_key + "&aktiven=" + aktiven, pingResponse)
	setTimeout("pinger()", ping_interval);
	
	if(first_ping) {
		//prikaži aktivnost povezave (10s)
		if(new Date().getTime() - last_response < 10000) {
			var date = new Date();
			
			var h = date.getHours();
			if(h<10) h = "0"+h;
			
			var m = date.getMinutes();
			if(m<10) m = "0"+m;
			
			var s = date.getSeconds();
			if(s<10) s = "0"+s;
			
			$('status').innerHTML = "<font color='green'>Povezava s sistemom je aktivna <!--("+h+" : "+m+" : "+s+")-->.</font>";
		}
		else {
			$('status').innerHTML = "<font color='red'>Povezava ne deluje!</font>";
		}
	}
	else {
		$('status').innerHTML = "<font color='orange'>Vzpostavljam povezavo...</font>";
	}
}
window.onload = pinger;

function pingResponse(txt) {
	first_ping = true;
	
	var lines = txt.split("\n");
	if(lines[0] == "ok") {
		last_response = new Date().getTime();
		
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
			barva = "#eee";
			if(delegati[i][1] > 0) {
				barva = barve_glasu[delegati[i][1] - 1];
				glasovi[delegati[i][1] - 1]++;
			}
			
			var delegat_id = delegati[i][0];
			
			//delegat je v indeksu
			if(typeof(delegati_index[delegat_id]) == "object") {
				var delegat = delegati_index[delegat_id][0] + " (" + delegati_index[delegat_id][1] + ")";
				html += "<div style='background-color:"+barva+";'>" + delegat + "</div> ";
				
				//rod v obliki Ime (kratica)
				var rod = delegati_index[delegat_id][2] + " (" + delegati_index[delegat_id][1] + ")";
				if(rodovi.indexOf(rod) < 0) rodovi.push(rod);
				
				//obmocje v obliki Ime (kratica)
				var obmocje = delegati_index[delegat_id][4] + " (" + delegati_index[delegat_id][3] + ")";
				if(obmocja.indexOf(obmocje) < 0) obmocja.push(obmocje);
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
			
			$('glasov').innerHTML = vsota;
			
			$('glasovi-za').innerHTML = glasovi[0];
			$('glasovi-za').nextSibling.innerHTML = pct(glasovi[0]/vsota) + "%";
			
			$('glasovi-proti').innerHTML = glasovi[1];
			$('glasovi-proti').nextSibling.innerHTML = pct(glasovi[1]/vsota) + "%";
			
			$('glasovi-vzdrzani').innerHTML = glasovi[2];
			$('glasovi-vzdrzani').nextSibling.innerHTML = pct(glasovi[2]/vsota) + "%";
			
			$('udelezba').innerHTML = pct(vsota/delegati.length);
			
			$('trenuten-potek').style.display = 'block';
			
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
		if(delegati.length >= pogoji_sklepcnost[0] && rodovi.length >= pogoji_sklepcnost[1] && obmocja.length >= pogoji_sklepcnost[0]) {
			//zeleno
			$('sklepcnost-div').style.backgroundColor = barve_glasu[0];
			$('sklepcnost-div').innerHTML = "Smo sklepčni";
		}
		else {
			//zeleno
			$('sklepcnost-div').style.backgroundColor = barve_glasu[1];
			$('sklepcnost-div').innerHTML = "Nismo sklepčni";
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
				//skrij zahvalo
				$('hvala').style.display = 'none';
				
				//ponastavi voting gumbe
				glas_aktiven = false;
				$('glas-1').style.backgroundColor = '';
				$('glas-2').style.backgroundColor = '';
				$('glas-3').style.backgroundColor = '';
				
		
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
				$('hvala').style.display = 'none';
			}
		}
	}
}

function glasuj(glas) {
	$('glas-' + glas).style.backgroundColor = barve_glasu[glas-1];
	
	//če gre za spremembo glasu
	if(glas_aktiven) {
		$('glas-' + zadnji_glas).style.backgroundColor = '';
	}
	
	//pošlji glas oz. pravi token v bazo
	ajax.post("glasuj.php", glasZabelezen, "v=" + vote_key + "&sklep=" + zadnji_sklep + "&token=" + vote_tokens[glas - 1]);
	
	glas_aktiven = true;
	zadnji_glas = glas;
	
	//aktiviraj glasovalni timer (neaktivno)
	//glas_timer = 10;
	//if(!glas_aktiven) timer();
}

function glasZabelezen(txt) {
	if(txt == "ok") {
		$('hvala').style.display = 'block';
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