<?php
include_once("../../../allegrofunction.php");

$data = json_decode(file_get_contents('php://input'), true);
$offerid = $data['offer'];
$shippingid = $data['shipping'];

$uuid = uuid();
$dane = array("modification"=>array("delivery"=>array("shippingRates"=>array("id"=>$shippingid,))),"offerCriteria"=>array(array("offers"=>array(array("id"=>$offerid)),"type"=>"CONTAINS_OFFERS")));
$url = 'https://api.allegro.pl/sale/offer-modification-commands/'.$uuid;
putPublic($url, $dane);

while (true){
    $res = getRequestPublic($url);
    $status = json_decode($res);
    if ($status->taskCount->failed > 0 || $status->taskCount->success > 0){
        break;
    }
    sleep(2);
}

$re = array("uuid" => $uuid, "response" => $status, "type" => "shipping");

print_r(json_encode($re));
?>