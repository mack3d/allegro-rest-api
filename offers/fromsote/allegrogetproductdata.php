<?php
include_once("../../allegrofunction.php");

$data = json_decode(file_get_contents('php://input'), true);

$ean = trim($data['ean']);

$res = getRequestPublic('https://api.allegro.pl/sale/products?mode=GTIN&phrase='.$ean);

print_r($res);
?>