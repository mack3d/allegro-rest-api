<?php
include_once("../../allegrofunction.php");

$allegro = new AllegroServices();

$data = json_decode(file_get_contents('php://input'), true);

$ean = trim($data['ean']);

$res = $allegro->sale("GET", "/products?mode=GTIN&phrase={$ean}");

print_r(json_encode($res));
