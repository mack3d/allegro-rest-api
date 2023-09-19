<?php
include_once("../../../allegrofunction.php");

$allegro = new AllegroServices();

$data = json_decode(file_get_contents('php://input'), true);

$uuids = $data['uuids'];
$stock = $uuids['stock'];
$price = $uuids['price'];
$status = $uuids['status'];

for ($i = 0; $i < count($stock); $i++) {
    $stock[$i] = $allegro->sale("GET", "/offer-quantity-change-commands/{$stock[$i]}");
}

for ($i = 0; $i < count($status); $i++) {
    $status[$i] = $allegro->sale("GET", "/offer-modification-commands/{$status[$i]}");
}

for ($i = 0; $i < count($price); $i++) {
    $price[$i] = $allegro->sale("GET", "/offer-price-change-commands/{$price[$i]}");
}

$uuids['stock'] = $stock;
$uuids['price'] = $price;
$uuids['status'] = $status;

print_r(json_encode($uuids));
