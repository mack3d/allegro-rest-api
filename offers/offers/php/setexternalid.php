<?php
include_once("../../../allegrofunction.php");

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['offer'];
$externalid = $data['externalid'];

$i = getRequestPublic('https://api.allegro.pl/sale/offers/'.$id);
$i = json_decode($i);

$externalid = array(
    "id" => $externalid,
);
$externalid = json_decode(json_encode($externalid));
$i->external = $externalid;

$tax = gettaxid($i->category->id);
$i->tax->percentage = ($tax->percentage != '')?$tax->percentage:'';
$i->tax->rate = (isset($tax->rate->id))?$tax->rate->id:'';
$i->tax->subject = (isset($tax->subject->id))?$tax->subject->id:'';
$i->tax->id = $tax->id;

$i = json_decode(json_encode($i),true);
$j = putPublic('https://api.allegro.pl/sale/offers/'.$id,$i);

print_r($j);
?>



<?php
/*include_once("../../../allegrofunction.php");

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['offer'];
$externalid = $data['externalid'];
$uuid = $data['uuid'];
$type = $data['type'];

$dane = array("external"=>array(array("id"=>$externalid)));

$url = 'https://api.allegro.pl/sale/product-offers/'.$id;
patch($url, $dane);

while (true){
    $i = getRequestPublic('https://api.allegro.pl/sale/offers/'.$id);
    $i = json_decode($i);

    if ($i->external->id == $externalid){
        break;
    }
    sleep(2);
}

$re = array("uuid" => $uuid, "status" => "success","type" => $type);

print_r(json_encode($re));*/
?>