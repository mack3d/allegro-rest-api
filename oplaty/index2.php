<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Allegro - opłata utrzymaniowa</title>
	<meta name="author" content="Maciej Krupiński">
</head>
<body id="body">
<textarea onchange="convert()" id="aukcje" name="aukcje" rows="1" cols="50"></textarea>

<ol id="lista">
</ol>

<script type="text/javascript">

function convert(){
    var aukcje = document.getElementById("aukcje");
    var aukcja = aukcje.value.split("Opłata utrzymaniowa");
    var lista = document.getElementById("lista");
    lista.innerHTML = "";
    e = [];
    for(var i = 0; i < aukcja.length; i++){
        if (aukcja[i] != 'undefined' & aukcja[i] != "" & aukcja[i].length > 0){
            numerek = aukcja[i].split("(");
            numerek = numerek[numerek.length-1].split(")");
            numerek = numerek[0];
            e.push(numerek);
        }
    }
    offers(e);
    aukcje.value = "";
}

function offers(e){
    if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var odpowiedz = JSON.parse(xmlhttp.responseText);
            
        }
    }
    var url = "&id=0";
    xmlhttp.open("POST","getalloffers.php",true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(url);
}


</script>