<?php
include_once("../../../allegrofunction.php");

$data = json_decode(file_get_contents('php://input'), true);

$offerid = $data['offer'];
$stock = $data['stock'];
$uuid = $data['uuid'];

$dane = array("offerCriteria"=>array(array("offers"=>array(array("id"=>$offerid)),"type"=>"CONTAINS_OFFERS")));
$mod = array("modification"=>array("changeType"=>"FIXED","value"=>$stock,));
$dane = array_merge($dane, $mod);

$url = 'https://api.allegro.pl/sale/offer-quantity-change-commands/'.$uuid;
putPublic($url, $dane);

while (true){
    $res = getRequestPublic($url);
    $status = json_decode($res);
    if ($status->taskCount->failed > 0 || $status->taskCount->success > 0){
        break;
    }
    sleep(2);
}

$re = array("uuid" => $uuid, "response" => $status, "type" => "stock");

print_r(json_encode($re));
?>