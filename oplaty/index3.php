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

async function convert(){
    var aukcje = document.getElementById("aukcje");
    var aukcja = aukcje.value.split("Opłata utrzymaniowa");

    for(var i = 0; i < aukcja.length; i++){
        var od = 'pusto';
        if (aukcja[i] != 'undefined' & aukcja[i] != "" & aukcja[i].length > 0){
            console.log(aukcja[i]);
            od = await getdataallegro(aukcja[i]);
        }
        console.log(od);
    }
    aukcje.value = "";
}



function getdataallegro(offersid){
    if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var odpowiedz = xmlhttp.responseText;
            return odpowiedz;
        }
    }
    var url = "&offerid="+offersid;
    xmlhttp.open("POST","getdataallegro.php",true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(url);
}

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