<?php
include_once("../../../allegrofunction.php");
$allegro = new AllegroServices();
$data = json_decode(file_get_contents('php://input'), true);

$offerid = $data['offer'];
$uuid = $data['uuid'];

$dane = array("offerCriteria" => array(array("offers" => array(array("id" => $offerid)), "type" => "CONTAINS_OFFERS")));
$i = $allegro->sale("GET", "/offers/{$offerid}");
$new_status = ($i->publication->status == "ENDED") ? "ACTIVATE" : "END";
$mod = array("publication" => array("action" => $new_status));
$dane = array_merge($dane, $mod);
$url = "/offer-publication-commands/{$uuid}";
$allegro->sale("PUT", $url, $dane);

while (true) {
    usleep(1500);
    $status = $allegro->sale("GET", $url);
    if ($status->taskCount->failed > 0 || $status->taskCount->success > 0) {
        break;
    }
}

$re = array("uuid" => $uuid, "new_status" => $new_status, "response" => $status, "type" => "status");

print_r(json_encode($re));
