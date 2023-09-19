<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>Allegro iSAT</title>
	<meta name="author" content="Maciej Krupiński">
	<link rel="stylesheet" href="style.css">
	<script src="scripts.js"></script>
</head>

<style>
	#newfods {
		position: fixed;
		margin: none;
		padding: none;
		top: 0;
		left: 0;
		background-color: black;
		opacity: 0.5;
		z-index: 1000;
		text-align: center;
		color: white;
		font-size: 15px;
		width: 100vw;
		height: 100vh;

		<?php

		if (isset($_GET['newfods'])) {
			echo "display:block;";
		} else {
			echo "display:none;";
		}
		?>
	}
</style>
<div id="blokuj">
	<div class="lds-facebook">
		<div></div>
		<div></div>
		<div></div>
	</div>
</div>

<body id="body">
	<div id="newfods"></div>

	<div id="dpd">
		<form method="post" action="dpdcsv.php">
			<input onclick="dpdcsv()" type="submit" value="generuj"><input name="od" type="date">
		</form>
	</div>
	<?php

	include_once("./allegrofunction.php");
	include_once("./orders.class.php");
	include_once("./database.class.php");

	session_start();

	@$szukajtext = $_GET['search'];

	if (!isset($_COOKIE['tokenn'])) {
		$auth = new AllegroOAuth2Client();
		$signInUrl = $auth->getAuthorizationUri();
		$logowanie = '<a id="loging" class="loguj" href="' . $signInUrl . '">Zaloguj do Allegro</a>';
		if (!empty($_GET['code'])) {
			$result = $auth->tokenRequest($_GET['code']);
			setcookie('tokenn', $result->access_token, time() + $result->expires_in);
			header("Location: ./");
		}
	} else {
		$logowanie = '<a id="getnew" class="loguj" href="?connectallegro=isat" onclick="blokuj()">Pobierz nowe</a>';
	}
	$allegro = new AllegroServices();
	$pdo = new DBconn();

	$fodadd = $pdo->prepare('INSERT INTO newallegroorders (fod,messagetoseller,buyerlogin,statusfod,paymentid,paymenttype,paymentprovider,paymentfinished,paymentpaid,deliverymethod,itemid,summary,bougthtime,filledintime,readytime) VALUES (:fod,:messagetoseller,:buyerlogin,:statusfod,:paymentid,:paymenttype,:paymentprovider,:paymentfinished,:paymentpaid,:deliverymethod,:itemid,:summary,:bougthtime,:filledintime,:readytime)');
	$fodupd = $pdo->prepare('UPDATE newallegroorders SET messagetoseller=:messagetoseller,buyerlogin=:buyerlogin,statusfod=:statusfod,paymentid=:paymentid,paymenttype=:paymenttype,paymentprovider=:paymentprovider,paymentfinished=:paymentfinished,paymentpaid=:paymentpaid,deliverymethod=:deliverymethod,itemid=:itemid,summary=:summary WHERE fod=:fod');
	$buyeradd = $pdo->prepare('INSERT INTO newallegrobuyer (fod,userid,email,username,personalIdentity,phoneNumber,street,city,postcode) VALUES (:fod,:userid,:email,:username,:personalIdentity,:phoneNumber,:street,:city,:postcode)');
	$messageadd = $pdo->prepare('INSERT INTO newallegromessage (fod,messagetoseller) VALUES (:fod,:messagetoseller)');
	$deliveryadd = $pdo->prepare('INSERT INTO newallegrodelivery (fod,addressname,street,city,postcode,companyname,phonenumber,methodid,methodname,pickuppoint,cost,smart,numberofpackages) VALUES (:fod,:addressname,:street,:city,:postcode,:companyname,:phonenumber,:methodid,:methodname,:pickuppoint,:cost,:smart,:numberofpackages)');
	$itemadd = $pdo->prepare('INSERT INTO newallegrolineitems (id,fod,offerid,offername,offerexternal,quantity,originalprice,price,boughtat) VALUES (:id,:fod,:offerid,:offername,:offerexternal,:quantity,:originalprice,:price,:boughtat)');
	$itemupd = $pdo->prepare('UPDATE newallegrolineitems SET fod=:fod,quantity=:quantity,originalprice=:originalprice,price=:price,boughtat=:boughtat WHERE id=:id');
	$invoiceadd = $pdo->prepare('INSERT INTO newallegroinvoice (fod,street,city,zipcode,companyname,companytaxid,naturalperson) VALUES (:fod,:street,:city,:zipcode,:companyname,:companytaxid,:naturalperson)');
	$surchargesadd = $pdo->prepare('INSERT INTO newallegrosurcharges (id,transactionid,fod,methodtype,methodprovider,finishedat,price) VALUES (:id,:transactionid,:fod,:methodtype,:methodprovider,:finishedat,:price)');
	$surchargesupd = $pdo->prepare('UPDATE newallegrosurcharges SET transactionid=:transactionid,methodtype=:methodtype,methodprovider=:methodprovider,finishedat=:finishedat,price=:price WHERE id=:id');
	$cancelupd = $pdo->prepare('UPDATE newallegroorders SET statusfod=:statusfod WHERE fod=:fod');
	$transactionidadd = $pdo->prepare('UPDATE newallegroorders SET transactionid=:transactionid WHERE paymentid=:paymentid');
	$doubleorder = $pdo->prepare('SELECT fod,buyerlogin,statusfod FROM newallegroorders WHERE buyerlogin=:buyerlogin AND bougthtime>:bougthtime');
	$szukajtowaru = $pdo->prepare('SELECT GROUP_CONCAT(DISTINCT(fod)) as fody FROM newallegrolineitems WHERE offername LIKE :szukaj OR offerexternal LIKE :szukaj');
	$szukajdpd = $pdo->prepare('SELECT GROUP_CONCAT(DISTINCT(fod)) as fody FROM newallegroorders WHERE deliverymethod LIKE :dm ORDER BY readytime DESC LIMIT 10');
	$szukajoferty = $pdo->prepare('SELECT GROUP_CONCAT(DISTINCT(fod)) as fody FROM newallegrolineitems WHERE offerid LIKE :szukaj');
	$szukajdeal = $pdo->prepare('SELECT GROUP_CONCAT(DISTINCT(fod)) as fody FROM newallegroorders WHERE transactionid LIKE :szukaj');
	$szukajshipmentsnum = $pdo->prepare('SELECT GROUP_CONCAT(DISTINCT(fod)) as fody FROM newallegroorders WHERE shipmentsnumber LIKE :shipmentsnumber');
	$szukajlogin = $pdo->prepare('SELECT GROUP_CONCAT(DISTINCT(fod)) as fody FROM newallegroorders LEFT JOIN newallegrobuyer USING(fod) WHERE newallegroorders.buyerlogin LIKE :szukaj OR newallegrobuyer.username LIKE :szukaj');
	$szukajwielopak = $pdo->prepare('SELECT GROUP_CONCAT(DISTINCT(fod)) as fody FROM newallegrodelivery WHERE numberofpackages IS NOT NULL AND numberofpackages>1');
	$szukajpickuppoint = $pdo->prepare('SELECT GROUP_CONCAT(DISTINCT(fod)) as fody FROM newallegrodelivery WHERE pickuppoint LIKE :pickuppoint');
	$szukajallegroone = $pdo->prepare('SELECT GROUP_CONCAT(DISTINCT(fod)) as fody FROM newallegroorders WHERE shipmenttime LIKE :shipmenttime AND deliverymethod LIKE "allegro one%"');
	$szukajphonenumber = $pdo->prepare('SELECT GROUP_CONCAT(DISTINCT(fod)) as fody FROM newallegrodelivery WHERE phonenumber LIKE :phonenumber');
	$szukajduplicat = $pdo->prepare('SELECT fod,buyerlogin,paymentfinished FROM newallegroorders WHERE buyerlogin=:buyerlogin AND paymentfinished>:paymentfinished');


	function street($street)
	{
		$street = (substr($street, 0, 3) == "ul.") ? trim(substr($street, 3)) : $street;
		$street = (substr($street, 0, 3) == "ul ") ? trim(substr($street, 3)) : $street;
		return $street;
	}

	function bladdodziennika($wpis)
	{
		$plik = fopen("bledy.txt", "a+");
		fwrite($plik, date('c') . ' ' . $wpis . "\r\n \r\n");
		fclose($plik);
	}

	function polandtime($t)
	{
		if ($t != '') {
			$gmt_houre = date("H") - gmdate("H");
			$date = new DateTime($t);
			$date->modify('+' . $gmt_houre . ' hour');
			return $date->format('Y-m-d H:i:s');
		} else {
			return $t;
		}
	}

	function fodadd($order, $itemid, $messageToSeller, $boughtAt, $finishedAt)
	{
		global $pdo, $fodadd;
		$fodadd->bindValue(':fod', $order->id, PDO::PARAM_STR);
		$fodadd->bindValue(':messagetoseller', $messageToSeller, PDO::PARAM_INT);
		$fodadd->bindValue(':buyerlogin', $order->buyer->login, PDO::PARAM_STR);
		$fodadd->bindValue(':statusfod', $order->status, PDO::PARAM_STR);
		$fodadd->bindValue(':paymentid', @$order->payment->id, PDO::PARAM_STR);
		$fodadd->bindValue(':paymenttype', @$order->payment->type, PDO::PARAM_STR);
		$fodadd->bindValue(':paymentprovider', @$order->payment->provider, PDO::PARAM_STR);
		$fodadd->bindValue(':paymentfinished', polandtime(@$order->payment->finishedAt), PDO::PARAM_STR);
		$fodadd->bindValue(':paymentpaid', @$order->payment->paidAmount->amount, PDO::PARAM_STR);
		$fodadd->bindValue(':deliverymethod', @$order->delivery->method->name, PDO::PARAM_STR);
		$fodadd->bindValue(':itemid', $itemid, PDO::PARAM_STR);
		$fodadd->bindValue(':summary', $order->summary->totalToPay->amount, PDO::PARAM_STR);
		$fodadd->bindValue(":bougthtime", polandtime($boughtAt), PDO::PARAM_STR);
		$fodadd->bindValue(":filledintime", polandtime($finishedAt), PDO::PARAM_STR);
		$fodadd->bindValue(":readytime", polandtime($finishedAt), PDO::PARAM_STR);
		try {
			$fodadd->execute();
		} catch (PDOException $e) {
			bladdodziennika('fodadd' . $e->getMessage());
		}
	}

	function buyeradd($fodid, $buyer)
	{
		global $pdo, $buyeradd;
		$buyerstreet = (!is_null($buyer->address)) ? street($buyer->address->street) : NULL;
		$buyercity = (!is_null($buyer->address)) ? $buyer->address->city : NULL;
		$buyerpostc = (!is_null($buyer->address)) ? $buyer->address->postCode : NULL;
		$buyeradd->bindValue(':fod', $fodid, PDO::PARAM_STR);
		$buyeradd->bindValue(':userid', $buyer->id, PDO::PARAM_INT);
		$buyeradd->bindValue(':email', $buyer->email, PDO::PARAM_STR);
		$buyeradd->bindValue(':username', trim($buyer->firstName . ' ' . $buyer->lastName . ' ' . $buyer->companyName), PDO::PARAM_STR);
		$buyeradd->bindValue(':personalIdentity', $buyer->personalIdentity, PDO::PARAM_STR);
		$buyeradd->bindValue(':phoneNumber', $buyer->phoneNumber, PDO::PARAM_STR);
		$buyeradd->bindValue(':street', $buyerstreet, PDO::PARAM_STR);
		$buyeradd->bindValue(':city', $buyercity, PDO::PARAM_STR);
		$buyeradd->bindValue(':postcode', $buyerpostc, PDO::PARAM_STR);
		try {
			$buyeradd->execute();
		} catch (PDOException $e) {
			bladdodziennika('buyeradd' . $e->getMessage());
		}
	}

	function messageadd($fodid, $fodmessageToSeller)
	{
		global $pdo, $messageadd;
		$messageadd->bindValue(':fod', $fodid, PDO::PARAM_STR);
		$messageadd->bindValue(':messagetoseller', trim($fodmessageToSeller), PDO::PARAM_STR);
		try {
			$messageadd->execute();
		} catch (PDOException $e) {
			bladdodziennika('messageadd' . $e->getMessage());
		}
	}

	function invoiceadd($fodid, $invoice)
	{
		global $pdo, $invoiceadd;
		$companyname = NULL;
		$companytaxid = NULL;
		$naturalperson = NULL;
		if (!is_null($invoice->address->company)) {
			$companyname = $invoice->address->company->name;
			$companytaxid = $invoice->address->company->taxId;
		}
		if (!is_null($invoice->address->naturalPerson)) {
			$naturalperson = $invoice->address->naturalPerson->firstName . ' ' . $invoice->address->naturalPerson->lastName;
		}
		$invoiceadd->bindValue(':fod', $fodid, PDO::PARAM_STR);
		$invoiceadd->bindValue(':street', $invoice->address->street, PDO::PARAM_STR);
		$invoiceadd->bindValue(':city', $invoice->address->city, PDO::PARAM_STR);
		$invoiceadd->bindValue(':zipcode', $invoice->address->zipCode, PDO::PARAM_STR);
		$invoiceadd->bindValue(':companyname', $companyname, PDO::PARAM_STR);
		$invoiceadd->bindValue(':companytaxid', $companytaxid, PDO::PARAM_STR);
		$invoiceadd->bindValue(':naturalperson', $naturalperson, PDO::PARAM_STR);
		try {
			$invoiceadd->execute();
		} catch (PDOException $e) {
			bladdodziennika('invoiceadd' . $e->getMessage());
		}
	}

	function deliveryadd($fodid, $delivery)
	{
		global $pdo, $deliveryadd;
		$pickuppoint = (!is_null($delivery->pickupPoint)) ? $delivery->pickupPoint->name . ' ' . $delivery->pickupPoint->address->zipCode . ' ' . $delivery->pickupPoint->address->city . ' ' . $delivery->pickupPoint->address->street : NULL;
		$deliveryadd->bindValue(':fod', $fodid, PDO::PARAM_STR);
		$deliveryadd->bindValue(':addressname', $delivery->address->firstName . ' ' . $delivery->address->lastName, PDO::PARAM_STR);
		$deliveryadd->bindValue(':street', $delivery->address->street, PDO::PARAM_STR);
		$deliveryadd->bindValue(':city', $delivery->address->city, PDO::PARAM_STR);
		$deliveryadd->bindValue(':postcode', $delivery->address->zipCode, PDO::PARAM_STR);
		$deliveryadd->bindValue(':companyname', $delivery->address->companyName, PDO::PARAM_STR);
		$deliveryadd->bindValue(':phonenumber', $delivery->address->phoneNumber, PDO::PARAM_STR);
		$deliveryadd->bindValue(':methodid', $delivery->method->id, PDO::PARAM_STR);
		$deliveryadd->bindValue(':methodname', $delivery->method->name, PDO::PARAM_STR);
		$deliveryadd->bindValue(':pickuppoint', $pickuppoint, PDO::PARAM_STR);
		$deliveryadd->bindValue(':cost', $delivery->cost->amount, PDO::PARAM_STR);
		$deliveryadd->bindValue(':smart', $delivery->smart, PDO::PARAM_STR);
		$deliveryadd->bindValue(':numberofpackages', $delivery->calculatedNumberOfPackages, PDO::PARAM_STR);
		try {
			$deliveryadd->execute();
		} catch (PDOException $e) {
			bladdodziennika('deliveryadd' . $e->getMessage());
		}
	}

	function surchargesadd($fodid, $surcharges)
	{
		global $pdo, $surchargesadd, $surchargesupd;
		$surchargesadd->bindValue(':fod', $fodid, PDO::PARAM_STR);

		foreach ($surcharges as $surcharge) {
			$mapapiid = getRequestPublic('https://api.allegro.pl/payments/payment-id-mappings?paymentId=' . $surcharge->id);
			$mapapiid = json_decode($mapapiid);
			$transactionid = (!isset($mapapiid->errors)) ? $transactionid = $mapapiid->transactionId : NULL;

			$surchargesadd->bindValue(':id', $surcharge->id, PDO::PARAM_STR);
			$surchargesadd->bindValue(':transactionid', $transactionid, PDO::PARAM_STR);
			$surchargesadd->bindValue(':methodtype', $surcharge->type, PDO::PARAM_STR);
			$surchargesadd->bindValue(':methodprovider', $surcharge->provider, PDO::PARAM_STR);
			$surchargesadd->bindValue(':finishedat', $surcharge->finishedAt, PDO::PARAM_STR);
			$surchargesadd->bindValue(':price', $surcharge->paidAmount->amount, PDO::PARAM_STR);

			$surchargesupd->bindValue(':id', $surcharge->id, PDO::PARAM_STR);
			$surchargesadd->bindValue(':transactionid', $transactionid, PDO::PARAM_STR);
			$surchargesupd->bindValue(':methodtype', $surcharge->type, PDO::PARAM_STR);
			$surchargesupd->bindValue(':methodprovider', $surcharge->provider, PDO::PARAM_STR);
			$surchargesupd->bindValue(':finishedat', polandtime($surcharge->finishedAt), PDO::PARAM_STR);
			$surchargesupd->bindValue(':price', $surcharge->paidAmount->amount, PDO::PARAM_STR);
			try {
				$surchargesadd->execute();
			} catch (PDOException $e) {
				$surchargesupd->execute();
			}
		}
	}

	function itemadd($fodid, $lineitems)
	{
		global $pdo, $itemadd, $itemupd;
		$itemid = '';
		$itemadd->bindValue(':fod', $fodid, PDO::PARAM_STR);
		$itemupd->bindValue(':fod', $fodid, PDO::PARAM_STR);
		foreach ($lineitems as $item) {
			$external = (isset($item->offer->external->id)) ? $item->offer->external->id : NULL;
			$itemadd->bindValue(':id', $item->id, PDO::PARAM_STR);
			$itemadd->bindValue(':offerid', $item->offer->id, PDO::PARAM_INT);
			$itemadd->bindValue(':offername', $item->offer->name, PDO::PARAM_STR);
			$itemadd->bindValue(':offerexternal', $external, PDO::PARAM_STR);
			$itemadd->bindValue(':quantity', $item->quantity, PDO::PARAM_INT);
			$itemadd->bindValue(':originalprice', $item->originalPrice->amount, PDO::PARAM_STR);
			$itemadd->bindValue(':price', $item->price->amount, PDO::PARAM_STR);
			$itemadd->bindValue(':boughtat', polandtime($item->boughtAt), PDO::PARAM_STR);

			$itemupd->bindValue(':quantity', $item->quantity, PDO::PARAM_INT);
			$itemupd->bindValue(':originalprice', $item->originalPrice->amount, PDO::PARAM_STR);
			$itemupd->bindValue(':price', $item->price->amount, PDO::PARAM_STR);
			$itemupd->bindValue(':boughtat', $item->boughtAt, PDO::PARAM_STR);
			$itemupd->bindValue(':id', $item->id, PDO::PARAM_STR);
			try {
				$itemadd->execute();
			} catch (PDOException $e) {
				$itemupd->execute();
			}
			$itemid = ($itemid == '') ? $item->id : $itemid . ',' . $item->id;
		}
		return $itemid;
	}

	function duplicat($buyerlogin, $paymentfinished)
	{
		global $szukajduplicat;
		$szukajduplicat->bindValue(":buyerlogin", $buyerlogin, PDO::PARAM_STR);
		$szukajduplicat->bindValue(":paymentfinished", $paymentfinished, PDO::PARAM_STR);
		$szukajduplicat->execute();
		return $szukajduplicat->rowCount();
	}

	function addNewOrders($order)
	{
		global $pdo;
		$sprawdzczyistnieje = $pdo->query('SELECT fod FROM newallegroorders WHERE fod="' . $order->id . '"');
		if ($sprawdzczyistnieje->rowCount() == 0) {
			buyeradd($order->id, $order->buyer);
			deliveryadd($order->id, $order->delivery);
			if ($order->invoice != NULL) {
				if ($order->invoice->required == 1) invoiceadd($order->id, $order->invoice);
			}
			$messageToSeller = 0;
			if ($order->messageToSeller != NULL & strlen(trim($order->messageToSeller)) > 0) {
				messageadd($order->id, trim($order->messageToSeller));
				$messageToSeller = 1;
			}
			$itemid = itemadd($order->id, $order->lineItems);
			if (!is_null($order->surcharges)) surchargesadd($order->id, $order->surcharges);
			fodadd($order, $itemid, $messageToSeller, $order->lineItems[0]->boughtAt, $order->payment->finishedAt);
		}
	}

	//#############################################################################################################

	if (isset($_COOKIE['tokenn']) & isset($_GET['connectallegro'])) {
		$gte_time = date('Y-m-d\T\00:\00:\00.\0\0\0\Z', strtotime("-30 days"));
		$params = array('status' => array('READY_FOR_PROCESSING', 'CANCELLED'), 'fulfillment.status' => array('NEW', 'CANCELLED'), 'limit' => 1, 'lineItems.boughtAt.gte' => $gte_time);
		$orders = checkOrders($params);
		$limit = 100;
		for ($offset = intdiv($orders->totalCount, $limit); $offset >= 0; $offset--) {
			$params = array('status' => array('READY_FOR_PROCESSING', 'CANCELLED'), 'fulfillment.status' => array('NEW', 'CANCELLED'), 'offset' => $offset * $limit, 'limit' => $limit, 'lineItems.boughtAt.gte' => $gte_time);
			$orders = checkOrders($params);
			if ($orders->count > 0) {
				for ($i = $orders->count - 1; $i >= 0; $i--) {
					$order = $orders->checkoutForms[$i];
					if ($order->status == 'READY_FOR_PROCESSING' || (($order->status == 'CANCELLED' || $order->fulfillment->status == 'CANCELLED') && !is_null($order->payment) && ($order->payment->type == 'CASH_ON_DELIVERY' || isset($order->payment->paidAmount->amount)))) {
						addNewOrders($order);
					}
				}
			}
		}

		try {
			$pdo->exec('UPDATE newallegromessage SET messagetoseller = REPLACE(messagetoseller, "\n", " ") WHERE messagetoseller LIKE "%\n%"');
		} catch (PDOException $e) {
			bladdodziennika('usuwanie nowej linii ' . $e->getMessage());
		}
	}

	function customerReturns($allegro, $zwrot)
	{
		$r = '';
		$return = $allegro->orderBeta('GET', '/customer-returns?parcels.sender.phoneNumber=' . $zwrot);
		if (!isset($return->errors)) {
			if ($return->count == 0) {
				$return = $allegro->orderBeta('GET', '/customer-returns?referenceNumber=' . strtoupper($zwrot));
			}
		}
		if (!isset($return->errors)) {
			if ($return->count == 0) {
				$return = $allegro->orderBeta('GET', '/customer-returns?parcels.waybill=' . $zwrot);
			}
		}

		if (!isset($return->errors)) {
			if ($return->count != 0) {
				foreach ($return->customerReturns as $ret) {
					$r .= $ret->orderId . ',';
				}
			}
		}
		return $r;
	}


	$szukanefody = '';
	if ($szukajtext != '') {
		if (preg_match('/^(kod|KOD)\s.+$/', $szukajtext)) {
			$szukajtext = explode(' ', $szukajtext);
			$szukajtext = "%" . $szukajtext[1] . "%";
			$szukajtowaru->bindValue(":szukaj", $szukajtext, PDO::PARAM_STR);
			$szukajtowaru->execute();
			$szukanefody = $szukajtowaru->fetchall()[0]['fody'];
		} elseif (preg_match('/^(dpd|DPD)\s.+$/', $szukajtext)) {
			$szukajdpd->bindValue(":dm", "%dpd%", PDO::PARAM_STR);
			$szukajdpd->execute();
			$szukanefody = $szukajdpd->fetchall()[0]['fody'];
		} elseif (preg_match('/^(oferta|OFERTA)\s.+$/', $szukajtext)) {
			$szukajtext = explode(' ', $szukajtext);
			$szukajtext = "%" . $szukajtext[1] . "%";
			$szukajoferty->bindValue(":szukaj", $szukajtext, PDO::PARAM_STR);
			$szukajoferty->execute();
			$szukanefody = $szukajoferty->fetchall()[0]['fody'];
		} elseif (preg_match('/^(trans|TRANS)\s.+$/', $szukajtext)) {
			$szukajtext = explode(' ', $szukajtext);
			$szukajtext = "%" . $szukajtext[1] . "%";
			$szukajdeal->bindValue(":szukaj", $szukajtext, PDO::PARAM_STR);
			$szukajdeal->execute();
			$szukanefody = $szukajdeal->fetchall()[0]['fody'];
		} elseif (preg_match('/^(odb|ODB)\s.+$/', $szukajtext)) {
			$szukajtext = explode(' ', $szukajtext);
			$szukajtext = "%" . $szukajtext[1] . "%";
			$szukajlogin->bindValue(":szukaj", $szukajtext, PDO::PARAM_STR);
			$szukajlogin->execute();
			$szukanefody = $szukajlogin->fetchall()[0]['fody'];
		} elseif (preg_match('/^(numer|NUMER)\s.+$/', $szukajtext)) {
			$szukajtext = explode(' ', $szukajtext);
			$szukajtext = "%" . $szukajtext[1] . "%";
			$szukajshipmentsnum->bindValue(":shipmentsnumber", $szukajtext, PDO::PARAM_STR);
			$szukajshipmentsnum->execute();
			$szukanefody = $szukajshipmentsnum->fetchall()[0]['fody'];
		} elseif (preg_match('/^(paczkomat|PACZKOMAT)\s.+$/', $szukajtext)) {
			$szukajtext = explode(' ', $szukajtext);
			$szukajtext = "%" . $szukajtext[1] . "%";
			$szukajpickuppoint->bindValue(":pickuppoint", $szukajtext, PDO::PARAM_STR);
			$szukajpickuppoint->execute();
			$szukanefody = $szukajpickuppoint->fetchall()[0]['fody'];
		} elseif (preg_match('/^(datawysylki)\s.+$/', $szukajtext)) {
			$szukajtext = explode(' ', $szukajtext);
			$szukajtext = $szukajtext[1] . "%";
			$szukajallegroone->bindValue(":shipmenttime", $szukajtext, PDO::PARAM_STR);
			$szukajallegroone->execute();
			$szukanefody = $szukajallegroone->fetchall()[0]['fody'];
		} elseif (preg_match('/^(telefon|TELEFON)\s.+$/', $szukajtext)) {
			$szukajtext = explode(' ', $szukajtext);
			$szukajtext = "%" . $szukajtext[1] . "%";
			$szukajphonenumber->bindValue(":phonenumber", $szukajtext, PDO::PARAM_STR);
			$szukajphonenumber->execute();
			$szukanefody = $szukajphonenumber->fetchall()[0]['fody'];
		} elseif (preg_match('/^(zwrot|ZWROT)\s.+$/', $szukajtext)) {
			$szukajtext = explode(' ', $szukajtext);
			$szukanefody = customerReturns($allegro, $szukajtext[1]);
		} elseif (strtolower($szukajtext) == "wielopaki") {
			$szukajwielopak->execute();
			$szukanefody = $szukajwielopak->fetchall()[0]['fody'];
		}
	}


	echo '<nav>
<li><a href="index.php"><img src="./img/home1.png"></a></li>
<li>' . $logowanie . '</li>
<li><input id="search" type="text" placeholder="Szukaj" onkeypress="szukaj(event)" title="Zaweżanie szukania:
- kod 0123
- oferta 9876543210
- trans 12345678912
- numer /numer przesyłki/
- odb login/imie/nazwisko
- paczkomat numer_paczkoamtu
- telefon numer_telefonu" />
<li><a href=""><img id="smart" src="./img/smart.png"></a></li>
<li><a href="billing/"><img src="./img/payu.png"></a></li>
<li><a onclick="przesylki()"><img src="./img/przesylki.png"></a></li>
<li><a onclick="dpd()"><img src="./img/dpd.png"></a></li>
</nav><div id="gora">';

	$wtrakcie = '';
	if (isset($_COOKIE['tokenn'])) {
		$orderswait = $allegro->order('GET', '/checkout-forms?status=FILLED_IN&fulfillment.status=NEW');
		if ($orderswait->count != 0) {
			foreach ($orderswait->checkoutForms as $r) {
				$isduplicat = duplicat($r->buyer->login, $r->lineItems[0]->boughtAt);
				$colorduplicat = '';
				if ($isduplicat > 0) {
					$colorduplicat = 'style="color:blue;"';
				}
				$wtrakcie .= '<tr ' . $colorduplicat . ' onclick="czekamyallegro(\'' . $r->id . '\')"><td>' . date("y-m-d H:i:s", strtotime($r->lineItems[0]->boughtAt)) . '</td><td>' . $r->buyer->login . '</td><td>' . $r->buyer->firstName . ' ' . $r->buyer->lastName . '</td></tr>';
			}
		}

		$ordersbuy = $allegro->order('GET', '/checkout-forms?status=BOUGHT&fulfillment.status=NEW');
		if ($ordersbuy->count != 0) {
			foreach ($ordersbuy->checkoutForms as $r) {
				$isduplicat = duplicat($r->buyer->login, $r->lineItems[0]->boughtAt);
				$colorduplicat = '';
				if ($isduplicat > 0) {
					$colorduplicat = 'style="color:blue;"';
				}
				$wtrakcie .= '<tr ' . $colorduplicat . ' onclick="czekamyallegro(\'' . $r->id . '\')"><td>' . date("y-m-d H:i:s", strtotime($r->lineItems[0]->boughtAt)) . '</td><td>' . $r->buyer->login . '</td><td>' . $r->buyer->firstName . ' ' . $r->buyer->lastName . '</td></tr>';
			}
		}

		if ($wtrakcie != '') {
			echo '<table class="czekamy"><thead><tr><td colspan="4">Czekamy: (' . ($orderswait->count + $ordersbuy->count) . ')</td></tr></thead><tbody>';
			echo $wtrakcie;
			echo '</tbody></table>';
		}

		$ordersnewcanceled = $allegro->order('GET', '/checkout-forms?status=CANCELLED&fulfillment.status=NEW');
		$doanulowania = '';
		if ($ordersnewcanceled->count > 0) {
			foreach ($ordersnewcanceled->checkoutForms as $r) {
				$isduplicat = duplicat($r->buyer->login, $r->lineItems[0]->boughtAt);
				$colorduplicat = '';
				if ($isduplicat > 0) {
					$colorduplicat = 'style="color:blue;"';
				}
				$doanulowania .= '<tr  ' . $colorduplicat . ' onclick="czekamyallegro(\'' . $r->id . '\')"><td>' . date("y-m-d H:i:s", strtotime($r->lineItems[0]->boughtAt)) . '</td><td>' . $r->buyer->login . '</td><td>' . $r->buyer->firstName . ' ' . $r->buyer->lastName . '</td></tr>';
			}
			echo '<table class="czekamy"><thead><tr><td colspan="4">Do anulowania: (' . $ordersnewcanceled->count . ')</td></tr></thead><tbody>';
			echo $doanulowania;
			echo '</tbody></table>';
		}
	}

	$page = $_GET['page'] ?? 0;
	$orders = new orders();

	if ($szukanefody == '') {
		$ordersList = $orders->getOrders(30, $page);
	} else {
		$ordersList = $orders->findOrdersByIds($szukanefody);
	}

	echo '</div><div id="dol">';
	echo '<table class="gotowe"><thead><tr><td colspan="7">Zamówienia: (nowych ' . $orders->readyCountOrders . ')</td><td><a href="?page=0">&#171</a> <a href="?page=' . $page++ . '">&#8249</a> <a href="?page=' . $page-- . '">&#8250</a></td></tr></thead><tbody>';

	foreach ($ordersList as $order) {
		$order_items = count(explode(",", $order->itemid));
		$clasa = ($order->statusfod == 'READY_FOR_PROCESSING') ? "bold" : "";
		$clasa = ($order->statusfod == 'SENT') ? "orang" : $clasa;
		$clasa = ($order->statusfod == 'CANCELLED') ? "red" : $clasa;
		echo '<tr class="' . $clasa . '" onclick="fod(\'' . $order->fod . '\')"><td>' . $order->readytime . '</td><td>' . $order->buyerlogin . '</td><td>' . $order->deliverymethod . '</td><td>' . number_format($order->summary, 2, ',', ' ') . '</td><td>' . $order->transactionid . '</td><td>' . $order_items . ' pozycji</td></tr>';
	}

	echo '</tbody></table>';
	echo '</div>';

	?>
	<footer>
		<p>New Allegro</p>
	</footer>
</body>

</html>