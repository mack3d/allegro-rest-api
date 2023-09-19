<?php
include_once("../../../allegrofunction.php");

$allegro = new AllegroServices();

$offerids = substr($_POST['offerids'],0,-1);
$offerids = explode(",",$offerids);

$multi = array();
foreach ($offerids as $offer){
    $i = $allegro->sale("GET", "/offers/{$offer}")
    $i = array("offer"=>$i);
    $p = $allegro->other("POST", "/pricing/offer-fee-preview", $i);

    $quotes = $allegro->other("GET", "/pricing/offer-quotes?offer.id={$offer}");

    $is_smart = $allegro->sale('GET', "/offers/{$offer}/smart");

    array_push($multi, array("id" => $offer,"fee" => $p, "quotes" => $quotes, "is_smart" => $is_smart));
}

print_r(json_encode($multi));
