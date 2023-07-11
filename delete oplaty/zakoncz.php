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
$ilosc = json_decode(json_encode(array("ilosc" => $ilosc,"kod" => $kod)));
array_push($odp,$ilosc);

print_r(json_encode($odp));
?>