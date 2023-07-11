<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Allegro - opłata utrzymaniowa</title>
	<meta name="author" content="Maciej Krupiński">
</head>
<body id="body">
<textarea onchange="pobierzaukcje()" id="aukcje" name="aukcje" rows="1" cols="50"></textarea>

<ol id="lista">
</ol>

<script type="text/javascript">
function pobierzaukcje(){
    if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var odpowiedz = JSON.parse(xmlhttp.responseText);
            convert(odpowiedz);
        }
    }
    var url = "&offerid=";
    xmlhttp.open("POST","zakoncz.php",true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(url);
}

function wystaw(numer){
    //console.log(numer);
    window.open("https://allegro.pl/offer/"+numer+"/similar",'_blank');
}

function convert(alloffers){
    var alloffers = alloffers;
    var aukcje = document.getElementById("aukcje");
    var aukcja = aukcje.value.split("Wystawienie");
    var lista = document.getElementById("lista");
    lista.innerHTML = "";
    for(var i = 0; i < 10; i++){
    //for(var i = 0; i < aukcja.length; i++){
        if (aukcja[i] != 'undefined' & aukcja[i] != "" & aukcja[i].length > 0){
            numerek = aukcja[i].split("(");
            numerek = numerek[numerek.length-1].split(")");
            numerek = numerek[0];


            function findaukcja(allaukcja) {
                return allaukcja.id == numerek;
            }

            dane = alloffers.find(findaukcja);
            console.log(dane);




            var offername = aukcja[i].split("\n")[1];

            var li = document.createElement("li");

            var span = document.createElement("span");
            var textspan = document.createTextNode(offername);
            span.appendChild(textspan);
            span.style.width = "620px";
            span.style.display = "inline-block";

            var spanexternal = document.createElement("span");
            var textspanexternal = document.createTextNode("");
            spanexternal.appendChild(textspanexternal);
            spanexternal.style.width = "150px";
            spanexternal.style.display = "inline-block";
            spanexternal.setAttribute("id", "e"+numerek);

            li.appendChild(span);
            li.appendChild(spanexternal);
            li.style.marginBottom = "5px";
            li.setAttribute("id", numerek);
            lista.appendChild(li);
        }
    }
    aukcje.value = "";
}
</script>