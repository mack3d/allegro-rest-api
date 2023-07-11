<?php
include_once("../allegrofunction.php");

$pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4','root','');$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$offerid = $_POST['offerid'];
$odp = array();

$daneoferty = getRequestPublic('https://api.allegro.pl/sale/offers/'.$offerid);
$daneoferty = json_decode($daneoferty);
array_push($odp,$daneoferty);
$dane = array("publication"=>array("action"=>"END"),"offerCriteria"=>array(array("offers"=>array(array("id"=>$offerid)),"type"=>"CONTAINS_OFFERS")));
$info = putPublic('https://api.allegro.pl/sale/offer-publication-commands/'.uuid(), $dane);
$info = json_decode($info);
array_push($odp,$info);
$ilosc = '';
$kod = '';
if (isset($daneoferty->external->id)){
    $kod = $daneoferty->external->id;
}else{
    $nazwaarr=explode(' ', $daneoferty->name);
    $kod=preg_replace('/[^a-zA-Z0-9-+ ]/', '', end($nazwaarr));
}
$ilosc = $pdo->prepare('SELECT kodn,ilosc FROM fpp WHERE kodn=:kodn');
$ilosc->bindValue(":kodn", $kod, PDO::PARAM_INT);
$ilosc->execute();
if ($ilosc->rowCount()>0){
    $d = $ilosc->fetch();
    $ilosc = $d[1];
    $kod = $d[0];
}else{
    $ilosc = "brak danych.";
}

function dopasowane($offerid,$kod){
    $nazwa = getRequestPublic('https://api.allegro.pl/sale/offers?publication.status=ACTIVE&limit=1');
    $ilosc = json_decode($nazwa)->totalCount;
    $wszystkie = array();
    for($i=0;$i<ceil($ilosc/1000);$i++){
        $aukcje = getRequestPublic('https://api.allegro.pl/sale/offers?publication.status=ACTIVE&limit=1000&offset='.$i*1000);
        $wszystkie = array_merge($wszystkie,json_decode($aukcje)->offers);
    }
    $ret = array();
    foreach($wszystkie as $aukcja){
        if ($aukcja->id != $offerid){
            if(isset($aukcja->external->id)){
                if(strpos(strtoupper($aukcja->external->id),strtoupper($kod)) or strpos(strtoupper($aukcja->name),strtoupper($kod))){
                    array_push($ret,$aukcja);
                }
            }else{
                if(strpos(strtoupper($aukcja->name),strtoupper($kod))){
                    array_push($ret,$aukcja);
                }
            }
        }
    }
    $ret = json_decode(json_encode($ret));
    return $ret;
}

$ret = dopasowane($offerid,$kod);
array_push($odp,$ret);

$ilosc = json_decode(json_encode(array("ilosc" => $ilosc,"kod" => $kod)));
array_push($odp,$ilosc);

print_r(json_encode($odp));
?>