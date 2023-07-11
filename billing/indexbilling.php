<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Allegro iSAT Lista patności</title>
	<meta name="author" content="Maciej Krupiński">
	<link rel="stylesheet" href="style.css">
</head>
<body onload="lista()">
<input id="limit" type="number" onchange="lista()" value="20" min="1" max="100">
<input id="offset" type="number" onchange="lista()" value="1" min="1" max="1000">
<select id="group" onchange="lista(this.selectedIndex);">
<option value="ALL">Wszystkie</option>
<option value="INCOME">Wpłaty</option>
<option value="OUTCOME" selected>Wypłaty</option>
<option value="REFUND">Zwroty</option>
</select>
<input id="gte" type="date" onchange="lista()" value="<?php echo date('Y-m-d', strtotime('-7 day'));?>">
<input id="lte" type="date" onchange="lista()" value="<?php echo date('Y-m-d');?>">
<select id="operator" onchange="lista(this.selectedIndex);">
<option value="ALL" selected>Wszystkie</option>
<option value="PAYU">PayU</option>
<option value="P24">Przelewy24</option>
</select>
<input id="login" type="text" onchange="lista()" placeholder="Login">
<input type="button" value="Pokaż" onchange="lista()">
<form action="tmppdf.php" target="_blank">
<input id="drukujwybrane" type="submit" value="Drukuj wybrane">
<input id="zaznaczwszystko" onclick="getValue()" type="button" value="Zaznacz wszystkie">
<table id="lista"></table>
</form>
</body>
</html>
<script>
function getValue() {
    var checks = document.getElementsByClassName('checks');
    for (i=0;i<checks.length;i++){
        checks[i].checked=true;
    }
}

function lista(){
	var obrot = document.getElementById("obrot");
	var limit = document.getElementById("limit").value;
	var offset = document.getElementById("offset").value;
	var group = document.getElementById("group").value;
	var lte = document.getElementById("lte").value;
	var gte = document.getElementById("gte").value;
	var login = document.getElementById("login").value;
	var operator = document.getElementById("operator").value;
	var lista = document.getElementById("lista");
	var drukujwybrane = document.getElementById("drukujwybrane");
	var zaznaczwszystko = document.getElementById("zaznaczwszystko");

	if(group=="REFUND"){
		drukujwybrane.style.visibility = "visible"; 
		zaznaczwszystko.style.visibility = "visible"; 
	}else{
		drukujwybrane.style.visibility = "hidden"; 
		zaznaczwszystko.style.visibility = "hidden"; 
	}

	lista.innerText = '';
	if(limit!=''){
		if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				var odpowiedz = JSON.parse(xmlhttp.responseText);
				console.log(odpowiedz);
				var ile = Object.keys(odpowiedz);
				console.log(ile);
				var licznik = 0;
				var suma = 0;
				for(i=ile.length-1;i>=0;i--){
					suma+=parseFloat(odpowiedz[i].value.amount);
					var tr = document.createElement("tr");
					var tda = document.createElement("td");

					var lp = document.createTextNode(++licznik);
					var tdlp = document.createElement("td");

					var tdb = document.createElement("td");
					var occurredAt = document.createTextNode(odpowiedz[i].occurredAt);
					var tdc = document.createElement("td");
					var value = document.createTextNode(odpowiedz[i].value.amount);
					var tdd = document.createElement("td");
					var operator = document.createTextNode(odpowiedz[i].wallet.paymentOperator);
					var tde = document.createElement("td");
					var tdchb = document.createElement("td");

					if(typeof odpowiedz[i].payment !== 'undefined'){
						var payid = document.createElement("a");
						var t = document.createTextNode(odpowiedz[i].payment.id);
						payid.setAttribute("href", '../orders/order.php?paymentid='+odpowiedz[i].payment.id);
						payid.appendChild(t);

						var checkbox = document.createElement('input');
						checkbox.type = "checkbox";
						checkbox.className = "checks";
						checkbox.name = "cb"+i;
						checkbox.value = odpowiedz[i].payment.id;
						checkbox.id = odpowiedz[i].payment.id;
					}else{
						var payid = document.createElement("a");
						var t = document.createTextNode(odpowiedz[i].payout.id);
						payid.setAttribute("href", 'test.php?numer='+odpowiedz[i].payout.id+'&operator='+odpowiedz[i].wallet.paymentOperator+'&data='+odpowiedz[i].occurredAt+'&suma='+odpowiedz[i].value.amount);
						payid.setAttribute('target', '_blank');
						payid.appendChild(t);
						var checkbox = document.createElement('input');
						checkbox.type = "checkbox";
						checkbox.className = "checks";
						checkbox.name = "cb"+i;
						checkbox.value = odpowiedz[i].payout.id;
						checkbox.id = odpowiedz[i].payout.id;
					}
					var tde = document.createElement("td");
					if(typeof odpowiedz[i].participant !== 'undefined'){
						participantlogin = odpowiedz[i].participant.login;
					}else{
						participantlogin = '';
					}
					var login = document.createTextNode(participantlogin);
					tdlp.appendChild(lp);
					tr.appendChild(tdlp);
					tda.appendChild(payid);
					tr.appendChild(tda);
					tdb.appendChild(occurredAt);
					tr.appendChild(tdb);
					tdc.appendChild(value);
					tr.appendChild(tdc);
					tdd.appendChild(login);
					tr.appendChild(tdd);
					tde.appendChild(operator);
					tr.appendChild(tde);
					if(group=="REFUND"){
						tdchb.appendChild(checkbox);
						tr.appendChild(tdchb);
					}
					lista.appendChild(tr);
				}
				console.log(suma);
			}
		}
		var url = "&limit="+limit+'&group='+group+"&offset="+offset+'&gte='+gte+"&lte="+lte+'&login='+login+'&operator='+operator;
		xmlhttp.open("POST","getbilling.php",true);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xmlhttp.send(url);
	}
}


</script>