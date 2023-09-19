<?php
include_once("../../../allegrofunction.php");
$allegro = new AllegroServices();
$offerdata = trim($_POST['offerdata']);
$i = json_decode($offerdata);
$id = $i->id;

$i = json_decode(json_encode($i),true);
$j = $allegro->sale("PUT", "/offers/{$id}", $i);

print_r($j);
