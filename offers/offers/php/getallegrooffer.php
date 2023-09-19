<?php
include_once("../../../allegrofunction.php");

$allegro = new AllegroServices();

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];

$offer = $allegro->sale("/offers/{$id}");

echo json_encode($offer);
