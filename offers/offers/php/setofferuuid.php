<?php
include_once("../../../allegrofunction.php");
$allegro = new AllegroServices();
$data = json_decode(file_get_contents('php://input'), true);

$offerid = $data['offer'];
$params = $data['params'];
$type = $data['type'];

$uuid = uuid();
$new_status = '';
$dane = array("offerCriteria"=>array(array("offers"=>array(array("id"=>$offerid)),"type"=>"CONTAINS_OFFERS")));


if ($type == "stock"){
    $mod = array("modification"=>array("changeType"=>"FIXED","value"=>$params,));
    $dane = array_merge($dane, $mod);
    $allegro->sale("PUT", "/offer-quantity-change-commands/{$uuid}", $dane);
}

$re = array("uuid" => $uuid, "params" => $params, "type" => $type, 'result' => $status);

print_r(json_encode($re));
