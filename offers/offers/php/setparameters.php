<?php
include_once("../../../allegrofunction.php");

$offerdata = trim($_POST['offerdata']);
$i = json_decode($offerdata);
$id = $i->id;

$i = json_decode(json_encode($i),true);
$j = putPublic('https://api.allegro.pl/sale/offers/'.$id,$i);

print_r(json_encode($j));
?>