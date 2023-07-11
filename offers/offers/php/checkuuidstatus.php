<?php
include_once("../../../allegrofunction.php");

$data = json_decode(file_get_contents('php://input'), true);

$uuids = $data['uuids'];
$stock = $uuids['stock'];
$price = $uuids['price'];
$status = $uuids['status'];

for ($i = 0; $i < count($stock); $i++){
    $res = getRequestPublic('https://api.allegro.pl/sale/offer-quantity-change-commands/'.$stock[$i]);
    $stock[$i] = json_decode($res);
}

for ($i = 0; $i < count($status); $i++){
    $res = getRequestPublic('https://api.allegro.pl/sale/offer-modification-commands/'.$status[$i]);
    $status[$i] = json_decode($res);
}

for ($i = 0; $i < count($price); $i++){
    $res = getRequestPublic('https://api.allegro.pl/sale/offer-price-change-commands/'.$price[$i]);
    $price[$i] = json_decode($res);
}

$uuids['stock'] = $stock;
$uuids['price'] = $price;
$uuids['status'] = $status;

print_r(json_encode($uuids));
?>