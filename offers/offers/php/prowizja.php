<?php
include_once("../../../allegrofunction.php");

$data = json_decode(file_get_contents('php://input'), true);

$offer_ids = $data['offer_ids'];

$res = array();
foreach ($offer_ids as $offer){
    $i = getRequestPublic('https://api.allegro.pl/sale/offers/'.$offer);
    $i = array("offer"=>json_decode($i));
    $p = postPublic('https://api.allegro.pl/pricing/offer-fee-preview',$i);
    $p = json_decode($p);

    $q = getRequestPublic('https://api.allegro.pl/pricing/offer-quotes?offer.id='.$offer);
    $q = json_decode($q);
    
    $is_smart = allegro('GET', '/sale/offers/'.$offer.'/smart');
    $is_smart = json_decode($is_smart);

    array_push($res, array("id" => $offer,"feePreview" => $p,"quotes" => $q, "isSmart" => $is_smart->classification->fulfilled, "shippingRatesId" => $i['offer']->delivery->shippingRates->id));
}

print_r(json_encode($res));
?>