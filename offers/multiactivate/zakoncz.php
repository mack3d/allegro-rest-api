<?php
include_once("../allegrofunction.php");

include_once("../../../database.class.php");
$pdo = new DBconn();

$pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4','root','');$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$offerid = $_POST['offerid'];

$dane = array("publication"=>array("action"=>"END"),"offerCriteria"=>array(array("offers"=>array(array("id"=>$offerid)),"type"=>"CONTAINS_OFFERS")));
$info = putPublic('https://api.allegro.pl/sale/offer-publication-commands/'.uuid(), $dane);
print_r($info);
