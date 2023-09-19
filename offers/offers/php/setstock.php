<?php
include_once("../../../allegrofunction.php");
$allegro = new AllegroServices();
$data = json_decode(file_get_contents('php://input'), true);

$offerid = $data['offer'];
$stock = $data['stock'];
$uuid = $data['uuid'];

$dane = array("offerCriteria"=>array(array("offers"=>array(array("id"=>$offerid)),"type"=>"CONTAINS_OFFERS")));
$mod = array("modification"=>array("changeType"=>"FIXED","value"=>$stock,));
$dane = array_merge($dane, $mod);

$url = "/offer-quantity-change-commands/{$uuid}";

$allegro->sale("PUT", $url, $dane);

while (true){
    $status = $allegro->sale("GET", $url);
    if ($status->taskCount->failed > 0 || $status->taskCount->success > 0){
        break;
    }
    sleep(2);
}

$re = array("uuid" => $uuid, "response" => $status, "type" => "stock");

print_r(json_encode($re));
