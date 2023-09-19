<?php
include_once("../../../allegrofunction.php");

$allegro = new AllegroServices();

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['offer'];
$externalid = $data['externalid'];

$i = $allegro->sale("GET", "/offers/{$id}");

$externalid = array(
    "id" => $externalid,
);
$externalid = json_decode(json_encode($externalid));
$i->external = $externalid;

$tax = gettaxid($i->category->id);
$i->tax->percentage = ($tax->percentage != '') ? $tax->percentage : '';
$i->tax->rate = (isset($tax->rate->id)) ? $tax->rate->id : '';
$i->tax->subject = (isset($tax->subject->id)) ? $tax->subject->id : '';
$i->tax->id = $tax->id;

$i = json_decode(json_encode($i), true);
$j = $allegro->sale("PUT", "/offers/{$id}", $i);

print_r(json_encode($j));
