<?php
include_once("../../../allegrofunction.php");

$allegro = new AllegroServices();

$data = json_decode(file_get_contents('php://input'), true);
$offerid = $data['offer'];
$shippingid = $data['shipping'];

$uuid = uuid();
$dane = array("modification" => array("delivery" => array("shippingRates" => array("id" => $shippingid,))), "offerCriteria" => array(array("offers" => array(array("id" => $offerid)), "type" => "CONTAINS_OFFERS")));
$allegro->sale("PUT", "/offer-modification-commands/{$uuid}", $dane);

while (true) {
    $status = $allegro->sale("GET", "/offer-modification-commands/{$uuid}");
    if ($status->taskCount->failed > 0 || $status->taskCount->success > 0) {
        break;
    }
    sleep(2);
}

$re = array("uuid" => $uuid, "response" => $status, "type" => "shipping");

print_r(json_encode($re));
