<?php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

include_once("allegrofunction.php");

$pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4','root','');$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$fodybeznum = $pdo->prepare('SELECT fod FROM newallegroorders WHERE statusfod="COMPLETING" AND deliverymethod NOT LIKE :deliverymethod');
$delivery = $pdo->prepare('UPDATE newallegroorders SET statusfod="SENT", shipmentsnumber=:shipmentsnumber, shipmenttime=:shipmenttime WHERE fod=:fod');

$cont = 0;

	if(isset($_COOKIE['tokenn'])){
		$fodybeznum->bindValue(":deliverymethod", "%osobisty%", PDO::PARAM_STR);
		$fodybeznum->execute();
		foreach($fodybeznum->fetchAll() as $fodbeznum){
			$shipments = getRequestPublic('https://api.allegro.pl/order/checkout-forms/'.$fodbeznum['fod'].'/shipments');
			$odp = json_decode($shipments);
			if(isset($odp->shipments)){
				if(count($odp->shipments)!=0){
					$delivery->bindValue(":shipmentsnumber", $odp->shipments[0]->waybill, PDO::PARAM_STR);
					$delivery->bindValue(":shipmenttime", $odp->shipments[0]->createdAt, PDO::PARAM_STR);
					$delivery->bindValue(":fod", $fodbeznum['fod'], PDO::PARAM_STR);
					$delivery->execute();
					++$cont;
				}
			}
		}
		echo 'Wyslano: '.$cont;
	}else{
		echo "Zaloguj się !!!";
	}

?>