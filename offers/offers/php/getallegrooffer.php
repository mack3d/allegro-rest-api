<?php
include_once("../../../allegrofunction.php");

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];

$offer = getRequestPublic('https://api.allegro.pl/sale/offers/'.$id);
$offer = json_decode($offer);
    
echo json_encode($offer);
