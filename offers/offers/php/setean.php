<?php
include_once("../../../allegrofunction.php");

$allegro = new AllegroServices();

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['offer'];
$ean = $data['ean'];

$ean = array(
    "id" => '225693',
    "valuesIds" => array(),
    "values" => array(
        $ean,
    ),
    "rangeValue" => null,
);
$ean = json_decode(json_encode($ean));

$i = $allegro->sale("GET", "/offers/{$id}");
$parameters = $i->parameters;
array_push($parameters, $ean);
$i->parameters = $parameters;
$tax = gettaxid($i->category->id);
$i->tax->percentage = ($tax->percentage != null) ? $tax->percentage : null;
$i->tax->rate = (isset($tax->rate->id)) ? $tax->rate->id : null;
$i->tax->subject = (isset($tax->subject->id)) ? $tax->subject->id : null;
$i->tax->id = $tax->id;

$i = json_decode(json_encode($i), true);
$j = $allegro->sale("PUT", "/offers/{$id}", $i);

print_r(json_encode($j));
