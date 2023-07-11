<?php
include_once("../../../allegrofunction.php");

$data = json_decode(file_get_contents('php://input'), true);

$offerid = $data['offer'];
$uuid = $data['uuid'];

$dane = array("offerCriteria"=>array(array("offers"=>array(array("id"=>$offerid)),"type"=>"CONTAINS_OFFERS")));
$i = getRequestPublic('https://api.allegro.pl/sale/offers/'.$offerid);
$i = json_decode($i);
$new_status = ($i->publication->status == "ENDED")?"ACTIVATE":"END";
$mod = array("publication"=>array("action" => $new_status));
$dane = array_merge($dane, $mod);
$url = 'https://api.allegro.pl/sale/offer-publication-commands/'.$uuid;
putPublic($url, $dane);

while (true){
    usleep(1500);
    $res = getRequestPublic($url);
    $status = json_decode($res);
    if ($status->taskCount->failed > 0 || $status->taskCount->success > 0){
        break;
    }
}

$re = array("uuid" => $uuid, "new_status" => $new_status, "response" => $status, "type" => "status");

print_r(json_encode($re));
?>