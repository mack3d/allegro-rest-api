<?php
include_once("../../../allegrofunction.php");

$offerids = substr($_POST['offerids'],0,-1);
$offerids = explode(",",$offerids);

$multi = array();
foreach ($offerids as $offer){
    $i = getRequestPublic('https://api.allegro.pl/sale/offers/'.$offer);
    $i = array("offer"=>json_decode($i));
    $p = postPublic('https://api.allegro.pl/pricing/offer-fee-preview',$i);
    $p = json_decode($p);

    $quotes = getRequestPublic('https://api.allegro.pl/pricing/offer-quotes?offer.id='.$offer);
    $quotes = json_decode($quotes);

    $is_smart = allegro('GET', '/sale/offers/'.$offer.'/smart');
    $is_smart = json_decode($is_smart);

    array_push($multi, array("id" => $offer,"fee" => $p, "quotes" => $quotes, "is_smart" => $is_smart));
}

print_r(json_encode($multi));
?>