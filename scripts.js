function fod(numer){
	window.location = "orders/order.php?fod="+numer;
}

function czekamyallegro(numer){
	window.open("https://allegro.pl/moje-allegro/sprzedaz/zamowienia/?query="+numer, '_blank');
}

function szukaj(event){
	var event;
	var text = document.getElementById("search").value;
	if(event.keyCode == 13){
		if(text.length<3){alert('wprowadz minimum 3 znaki');}
		else{window.location.href = "index.php?search="+text;}
	}
}

function przesylki(){
	var body = document.getElementById("body");
	var wys = body.clientHeight;
	var sze = body.clientWidth;
	document.getElementById("blokuj").style.height = wys+"px";
	document.getElementById("blokuj").style.width = sze+"px";
	document.getElementById("blokuj").style.display = "block";
	if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById("blokuj").style.display = "none";
			var odpowiedz = xmlhttp.responseText;
			alert(odpowiedz);
			window.location.href = "index.php";
		}
	}
	var url = "&inpost=1";
	xmlhttp.open("POST","przesylki.php",true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Connection", "close");
	xmlhttp.send(url);
}

function blokuj(){
	document.getElementById("blokuj").style.display = "inline";
}

function synchro(){
	var body = document.getElementById("body");
	var wys = body.clientHeight;
	var sze = body.clientWidth;
	document.getElementById("blokuj").style.height = wys+"px";
	document.getElementById("blokuj").style.width = sze+"px";
	document.getElementById("blokuj").style.display = "block";
	if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById("blokuj").style.display = "none";
			var odpowiedz = xmlhttp.responseText;
			alert(odpowiedz);
			window.location.href = "index.php";
		}
	}
	var url = "&s=sync";
	xmlhttp.open("POST","synchro.php",true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Connection", "close");
	xmlhttp.send(url);
}

function dpd(){
	var dpd = document.getElementById("dpd");
	var wys = body.clientHeight;
	var sze = body.clientWidth;
	document.getElementById("dpd").style.height = wys+"px";
	document.getElementById("dpd").style.width = sze+"px";
	document.getElementById("dpd").style.display = "block";
}

function dpdcsv(){
	var dpd = document.getElementById("dpd");
	document.getElementById("dpd").style.display = "none";
}