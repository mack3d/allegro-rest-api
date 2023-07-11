<?php
include_once("../allegrofunction.php");

$offerid = numerek($_POST['offerid']);

function numerek($tytul){
    $tytul = explode('(',$tytul);
    $tytul = explode(')',$tytul[1]);
    return $tytul[0];
}

$resp = array("id"=>$offerid);
print_r(json_encode($resp));

//$info = getRequestPublic('https://api.allegro.pl/sale/offers/'.$oferta);










/*
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