<?php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

include_once("./allegrofunction.php");
include_once("./database.class.php");

$pdo = new DBconn();

$fodybeznum = $pdo->prepare('SELECT fod FROM newallegroorders WHERE statusfod="COMPLETING" AND deliverymethod NOT LIKE :deliverymethod');
$delivery = $pdo->prepare('UPDATE newallegroorders SET statusfod="SENT", shipmentsnumber=:shipmentsnumber, shipmenttime=:shipmenttime WHERE fod=:fod');

$countOfAddNumbers = 0;

if (isset($_COOKIE['tokenn'])) {
	$fodybeznum->bindValue(":deliverymethod", "%osobisty%", PDO::PARAM_STR);
	$fodybeznum->execute();
	foreach ($fodybeznum->fetchAll() as $fodbeznum) {
		$shipments = getRequestPublic('https://api.allegro.pl/order/checkout-forms/' . $fodbeznum['fod'] . '/shipments');
		$odp = json_decode($shipments);
		if (isset($odp->shipments)) {
			if (count($odp->shipments) != 0) {
				$delivery->bindValue(":shipmentsnumber", $odp->shipments[0]->waybill, PDO::PARAM_STR);
				$delivery->bindValue(":shipmenttime", $odp->shipments[0]->createdAt, PDO::PARAM_STR);
				$delivery->bindValue(":fod", $fodbeznum['fod'], PDO::PARAM_STR);
				$delivery->execute();
				++$countOfAddNumbers;
			}
		}
	}
	echo json_encode(array("msg" => 'Wyslano: ' . $countOfAddNumbers));
} else {
	echo json_encode(array("msg" => "Zaloguj się !!!"));
}
