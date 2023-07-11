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
function zakoncz(numer){
    console.log(numer);
    var button = document.getElementById("z"+numer);
    var buttonw = document.getElementById("w"+numer);
    var external = document.getElementById("e"+numer);

    if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var odpowiedz = JSON.parse(xmlhttp.responseText);
            console.log(odpowiedz);
            if (odpowiedz[1].errors == 'undefined'){
                alert(numer+" ERROR");
                console.log(odpowiedz[1].errors);
            }else {
                dane = odpowiedz[0];
                ilosc = odpowiedz[2];
                button.setAttribute("disabled","disabled");
                buttonw.removeAttribute("disabled");
                buttonw.setAttribute("onClick", "wystaw("+numer+")");
                console.log(dane.external)
                if (dane.external != null){
                    if (typeof dane.external.id !== 'undefined'){
                        console.log(dane.external.id);
                        external.innerText = dane.external.id + " x " + ilosc.ilosc;
                    }
                }else{
                    external.innerText = ilosc.kod + " x " + ilosc.ilosc;
                }

            }
        }
    }
    var url = "&offerid="+numer;
    xmlhttp.open("POST","zakoncz.php",true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(url);
}

function wystaw(numer){
    console.log(numer);
    window.open("https://allegro.pl/offer/"+numer+"/similar",'_blank');
}

function convert(){
    var aukcje = document.getElementById("aukcje");
    var aukcja = aukcje.value.split("Opłata utrzymaniowa");
    var lista = document.getElementById("lista");
    lista.innerHTML = "";
    for(var i = 0; i < aukcja.length; i++){
        if (aukcja[i] != 'undefined' & aukcja[i] != "" & aukcja[i].length > 0){
            numerek = aukcja[i].split("(");
            numerek = numerek[numerek.length-1].split(")");
            numerek = numerek[0];
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

            var buttonz = document.createElement("button");
            var textbuttonz = document.createTextNode("Zakończ");
            buttonz.appendChild(textbuttonz);
            buttonz.setAttribute("id", "z"+numerek);
            buttonz.setAttribute("onClick", "zakoncz("+numerek+")");
            
            var buttonw = document.createElement("button");
            var textbuttonw = document.createTextNode("Wystaw");
            buttonw.appendChild(textbuttonw);
            buttonw.setAttribute("id", "w"+numerek);
            buttonw.setAttribute("disabled","disabled");

            li.appendChild(span);
            li.appendChild(spanexternal);
            li.appendChild(buttonz);
            li.appendChild(buttonw);
            li.style.marginBottom = "5px";
            li.setAttribute("id", numerek);
            lista.appendChild(li);
        }
    }
    aukcje.value = "";
}


/*
function getdataallegro(offersid){
    console.log(offersid);
    for (var i = 0; i < offersid.length; i++){
        if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                var odpowiedz = xmlhttp.responseText;
                console.log(odpowiedz);
            }
        }
        var url = "&offerid="+offersid[i];
        xmlhttp.open("POST","getdataallegro.php",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(url);
    }
}*/

</script>


<?php
/*
include_once("../allegrofunction.php");
session_start();
if(!isset($_SESSION["uuid"])){$_SESSION["uuid"] = uuid();}

@$aukcje = $_POST['aukcje'];

$aukcje = explode('Opłata utrzymaniowa',$aukcje);

$numeryofert = array();

foreach ($aukcje as $aukcja){
    $num = trim(numerek($aukcja));
    if ($num != ""){
        array_push($numeryofert,$num);
    }
}

function numerek($tytul){
    $tytul = explode('(',$tytul);
    $tytul = explode(')',$tytul[1]);
    return $tytul[0];
}

/*
function zakoncz($oferta){
    $dane = array("publication"=>array("action"=>"END"),"offerCriteria"=>array(array("offers"=>array(array("id"=>$oferta)),"type"=>"CONTAINS_OFFERS")));
    putPublic('https://api.allegro.pl/sale/offer-publication-commands/'.$_SESSION["uuid"], $dane);
}
*/

/*
print_r($numeryofert);
$plik = fopen('offers.json',"w+");
$offers = array();
foreach ($numeryofert as $oferta){
    $info = getRequestPublic('https://api.allegro.pl/sale/offers/'.$oferta);
    $i = json_decode($info);
    $ex = (isset($i->external->id))?$i->external->id:'';
    $offer = array("id" => $oferta, "name" => $i->name, "external" => $ex, "price" => $i->sellingMode->price->amount, "stock" => $i->stock->available);
    array_push($offers, $offer);
}
fwrite($plik,json_encode($offers));
fclose($plik);

/*
include_once("../allegrofunction.php");
session_start();
if(!isset($_SESSION["uuid"])){$_SESSION["uuid"] = uuid();}

@$aukcje = $_POST['aukcje'];

$aukcje = explode('Opłata utrzymaniowa',$aukcje);

function numerek($tytul){
    $tytul = explode('(',$tytul);
    $tytul = explode(')',$tytul[1]);
    return $tytul[0];
}

if(count($aukcje)>1){
    $oferty = array();
    foreach($aukcje as $aukcja){
        if(!empty($aukcja)){
            $numerek = numerek($aukcja);
            $oferta = array("id"=>$numerek);
            array_push($oferty,$oferta);
        }
    }
    $dane = array("publication"=>array("action"=>"END"),"offerCriteria"=>array(array("offers"=>$oferty,"type"=>"CONTAINS_OFFERS")));
    print_r($dane);
    putPublic('https://api.allegro.pl/sale/offer-publication-commands/'.$_SESSION["uuid"], $dane);
}

$info = getRequestPublic('https://api.allegro.pl/sale/offer-modification-commands/'.$_SESSION["uuid"].'/tasks');
print_r(json_decode($info));
*/
?>