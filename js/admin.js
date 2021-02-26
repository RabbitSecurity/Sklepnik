
function kopirajLink(e, id, key) {
	//vsaj za trenutek je treba pokazat div, ker drugač noče kopirat :)
	//pol na koncu, po kopiranju, pa skrijemo
	$('povezava').style.display = 'inline-block';
	$('povezava').style.top = (e.pageY+10) + 'px'
	$('povezava').style.left = (e.pageX+10) + 'px'
	
	$('povezava-link').value = 'https://bostjan.info/sklepnik/registracija.php?u=' + key;

	/* Get the text field */
	var copyText = $('povezava-link');

	/* Select the text field */
	copyText.select();
	copyText.setSelectionRange(0, 99999); /* For mobile devices */

	/* Copy the text inside the text field */
	document.execCommand("copy");
	
	//prikaži da je ok
	$('delegat-' + id).getElementsByTagName('td')[8].innerHTML = '✔️';
	$('povezava').style.display = 'none';
}

var sent_mails = [];
function posljiMail(id, key) {
	
	//pošiljanje dovoli samo 1x
	if(sent_mails.indexOf(id) > -1) return;
	sent_mails.push(id);
	
	$('delegat-' + id).getElementsByTagName('td')[9].innerHTML = '⏳';
	
	ajax.get('poslji-mail.php?u=' + key, function(response) {
		if(response == "ok") {
			$('delegat-' + sent_mails[sent_mails.length-1]).getElementsByTagName('td')[9].innerHTML = '✔️';
		}
		else {
			alert("Napaka pri pošiljanju maila!");
		}
	});
}

function brisiDelegata(id, ime) {
	
	//pokaži katerega:
	$('delegat-' + id).style.backgroundColor = 'red';
	
	var ask = confirm("Res želiš izbrisati delegata " + ime + "?");

	if(ask) {
		//skrij vrstico
		$('delegat-' + id).style.display = 'none';
		
		ajax.post("shrani.php?a=brisi-delegata", function(response) {
			console.log(response);
		}, "id=" + id);
	}
	else {
		$('delegat-' + id).style.backgroundColor = '#fff';
	}
}


//Knjižnica za urejanje tabel ala google documents

var wasEdited = false;
var wereEdited = [];
var editedValues = {};

var editedId = false;
var lastEdited = false;
var editedCol = -1;
var origValue = "";

function ediTable(id, editable_cols) {
	//skip first row
	var table = $(id);
	var trs = table.getElementsByTagName('tr');
	
	for(var i = 0; i < trs.length; i++) {
		var td = trs[i].getElementsByTagName('td');
		
		for(var j = 0; j < editable_cols.length; j++) {
			
			var data = {"elm": td[j], "index":j };
			td[j].addEventListener("dblclick", function() {editProperty(this.elm, this.index);}.bind(data));
		}
	}
}

function writeChange(id, col, value) {
	if(wereEdited.indexOf(id) < 0) {
		wereEdited.push(id);
	}
	
	if(editedValues[id] == null) {
		editedValues[id] = {};
	}
	editedValues[id][col] = value;
}

//Se kliče kot (this, id_stolpca) oz. preko addEventListener v ediTable
function editProperty(elm, col_id) {
	
	//grdo, ampak dela da dobiš id iz <tr> vn
	var delegat_id = elm.parentNode.getAttribute('id').split('-').pop();
	
	wasEdited = true;
	
	if(editedId === false) {
		editedId = delegat_id;
		lastEdited = editedId;
		
		editedCol = col_id
		console.log("Edit id = " + delegat_id + ", col = " + editedCol);
		
		var td = $('delegat-' + delegat_id).getElementsByTagName('td')[col_id];
		var value = td.innerHTML;
		origValue = value;
		
		td.innerHTML = "<input type='text' style='width:80%;text-align:left;' value='" + value + "' id='current-edit' onkeydown='editPropertyKeys(event);' onblur='endEdit();'>";
		
		$('current-edit').focus();
	}
}

//pomembne tipke v urejanju
function editPropertyKeys(e) {
	if(e.keyCode == 13) endEdit();
	if(e.keyCode == 27) cancelEdit();
	if(e.keyCode == 9) jumpNextColumn();
}

//končaj urejanje in shrani spremembo
function endEdit() {
		var editInput = $('current-edit');
		var editTd = editInput.parentNode;
		
		//zapiši na seznam sprememb
		writeChange(editedId, editedCol, editInput.value);
		
		editTd.innerHTML = editInput.value;

		lastEdited = editedId;
		wasWarning = false;
		editedId = false;
}

//prekliči urejanje
function cancelEdit() {
	var editInput = $('current-edit');
	var editTd = editInput.parentNode;
	
	editTd.innerHTML = origValue;
	editedId = -1;
}

function jumpNextColumn() {
	console.log('jump next?');
	if(lastEdited != null) {
		var col = editedCol;
		var id = lastEdited;
		console.log('jump next! '+lastEdited);
		
		setTimeout("editProperty("+lastEdited.toString()+", "+(editedCol+1)+");",50);
	}
}

function saveData() {
	
	//prepreči opozorilo zapustitve strani
	wasEdited = false;
	
	var data = JSON.stringify(editedValues);
	$('data-input').value = data;
	$('the-form').submit();
}
	
	

//Preprečitev zapustitve strani:
function noclose() {
	if(wasEdited) return "A si shranil podatke? ;)";
}
window.onbeforeunload = noclose;