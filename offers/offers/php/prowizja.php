<?php
include_once("../../../allegrofunction.php");

$allegro = new AllegroServices();
$data = json_decode(file_get_contents('php://input'), true);

$offer_ids = $data['offer_ids'];

$res = array();
foreach ($offer_ids as $offer) {
    $i = $allegro->sale("GET", "/offers/{$offer}");
    $i = array("offer" => $i);

    $p = $allegro->other("POST", '/pricing/offer-fee-preview', $i);

    $q = $allegro->other("GET", "/pricing/offer-quotes?offer.id={$offer}");

    $is_smart = allegro('GET', '/sale/offers/' . $offer . '/smart');
    $is_smart = json_decode($is_smart);

    array_push($res, array("id" => $offer, "feePreview" => $p, "quotes" => $q, "isSmart" => $is_smart->classification->fulfilled, "shippingRatesId" => $i['offer']->delivery->shippingRates->id));
}

print_r(json_encode($res));
