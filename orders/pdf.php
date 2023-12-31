<?php
require_once('../../tcpdf/tcpdf.php');

function nk($nazwa, $external)
{
	$kod = '';
	$external = trim($external);
	$nazwaarr = explode(' ', $nazwa);
	if (!is_null($external) & !empty($external)) {
		$kod = $external;
		if (!strpos($external, ' ')) {
			$arr = explode(' ', $external);
			if (strpos($nazwa, $external)) {
				$nazwa = trim(substr($nazwa, 0, strpos($nazwa, $external)));
			}
		} elseif (!strpos($external, ',')) {
			if (strpos($nazwa, $external)) {
				$nazwa = trim(substr($nazwa, 0, strpos($nazwa, $external)));
			}
		} elseif (!strpos($external, ';')) {
			if (strpos($nazwa, $external)) {
				$nazwa = trim(substr($nazwa, 0, strpos($nazwa, $external)));
			}
		} else {
			if (strpos($nazwa, $external)) {
				$nazwa = trim(substr($nazwa, 0, strpos($nazwa, $external)));
			}
		}
	} else {
		$kod = preg_replace('/[^a-zA-Z0-9-+ ]/', '', end($nazwaarr));
		$nazwa = trim(substr($nazwa, 0, strpos($nazwa, end($nazwaarr))));
	}
	$nazwa = (strpos(substr($nazwa, -1), '[')) ? substr($nazwa, 0, -1) : $nazwa;
	$kod = preg_replace('/[^a-zA-Z0-9-]/', ' ', $kod);
	$kod = preg_replace('/\s\s+/', ' ', $kod);
	$kod = preg_replace('/\s/', '<br>', $kod);
	return trim($nazwa) . '///' . $kod;
}

$fod = (isset($_GET['fod'])) ? $_GET['fod'] : $_POST['fod'];
$messageprint = (isset($_GET['message'])) ? $_GET['message'] : $_POST['message'];

include_once("../../database.class.php");
$pdo = new DBconn();

$order = $pdo->prepare('SELECT * FROM newallegroorders WHERE fod=:fod');
$order->bindValue(":fod", $fod, PDO::PARAM_STR);
$order->execute();
$order = $order->fetch();

$buyer = $pdo->prepare('SELECT * FROM newallegrobuyer WHERE fod=:fod');
$buyer->bindValue(":fod", $fod, PDO::PARAM_STR);
$buyer->execute();
$buyer = $buyer->fetch();

$lineitems = $pdo->prepare('SELECT * FROM newallegrolineitems WHERE FIND_IN_SET(id,:itemid)');
$lineitems->bindValue(":itemid", $order['itemid'], PDO::PARAM_STR);
$lineitems->execute();

$delivery = $pdo->prepare('SELECT * FROM newallegrodelivery WHERE fod=:fod');
$delivery->bindValue(":fod", $fod, PDO::PARAM_STR);
$delivery->execute();
$delivery = $delivery->fetch();

$message = $pdo->prepare('SELECT * FROM newallegromessage WHERE fod=:fod');
$message->bindValue(":fod", $fod, PDO::PARAM_STR);
$message->execute();
if ($message->rowCount() > 0) {
	$message = $message->fetch();
	$message = $message['messagetoseller'];
} else {
	$message = '';
}

$surcharges = $pdo->prepare('SELECT * FROM newallegrosurcharges WHERE fod=:fod');
$surcharges->bindValue(":fod", $_GET['fod'], PDO::PARAM_STR);
$surcharges->execute();
$surcharges = $surcharges->fetch();

$invoice = $pdo->prepare('SELECT * FROM newallegroinvoice WHERE fod=:fod');
$invoice->bindValue(":fod", $fod, PDO::PARAM_STR);
$invoice->execute();
$czyfaktura = $invoice->rowCount();

$faktura = '';
if ($czyfaktura != 0) {
	$invoice = $invoice->fetch();
	if ($invoice['companytaxid'] != "") {
		$faktura .= '<b>NIP: ' . $invoice['companytaxid'] . '</b><br>';
	}
	if ($invoice['companyname'] != "") {
		$faktura .= $invoice['companyname'] . '<br>';
	}
	if ($invoice['naturalperson'] != "") {
		$faktura .= $invoice['naturalperson'] . '<br>';
	}
	$faktura .= $invoice['street'] . '<br>' . $invoice['zipcode'] . ' ' . $invoice['city'];
} else {
	$faktura .= 'PARAGON';
}
$faktura .= '<br>' . $buyer['phoneNumber'];
$delivery = $pdo->prepare('SELECT * FROM newallegrodelivery WHERE fod=:fod');
$delivery->bindValue(":fod", $fod, PDO::PARAM_STR);
$delivery->execute();
$delivery = $delivery->fetch();
$wysylka = '';
$wysylka .= (trim($delivery['companyname']) != '') ? trim($delivery['companyname']) . '<br>' : '';
$wysylka .= $delivery['addressname'] . '<br>' . $delivery['street'] . '<br>' . $delivery['postcode'] . ' ' . $delivery['city'] . '<br>' . $delivery['phonenumber'];
$danedw = '<table><tr><td width="320">' . $faktura . '</td><td width="350">' . $wysylka . '</td></tr></table>';

$itemarr = array();

$topitem = array();
$bottomitem = array();
$topgroup = explode(" ", "12 33 14 24 16 31 94 58 13 17 99 07 40 81 84 77 20 21 36 34 35 25 43 76 44 09 83 49 54 41 83 82 51 59");
foreach ($lineitems->fetchAll() as $item) {
	$nk = nk($item['offername'], $item['offerexternal']);
	$nk = explode('///', $nk);
	$obj = new StdClass();
	$obj->offerid = $item['offerid'];
	$obj->name = $nk[0];
	$obj->kod = $nk[1];
	$obj->quantity = $item['quantity'];
	$obj->price = $item['price'];
	array_push($itemarr, $obj);
	//-----------------
	/*if(in_array(substr($nk[1],0,2),$topgroup)){
		array_push($topitem,$obj);
	}else{
		array_push($bottomitem,$obj);
	}*/
	//-----------------
}
function cmp($a, $b)
{
	return strcmp($a->kod, $b->kod);
}
usort($itemarr, "cmp");
ksort($itemarr);
/*
usort($topitem, "cmp");
ksort($topitem);
usort($bottomitem, "cmp");
ksort($bottomitem);
$itemarr = array_merge($topitem,$bottomitem);*/

$lp = 0;
$zatowary = 0;
$items = '<table><tr style="font-weight:bold;"><td width="22">Lp</td><td width="100">Id</td><td width="400">Nazwa</td><td width="58">Kod</td><td width="40">Ilość</td><td width="50">Cena</td></tr>';
foreach ($itemarr as $item) {
	$zatowary += $item->price * $item->quantity;
	$fontsize = ($item->quantity > 1) ? "14px" : "12px";
	$items .= '<tr><td>' . ++$lp . '</td><td style="font-size: 9px;">(' . $item->offerid . ')</td><td>' . $item->name . '</td><td>' . $item->kod . '</td><td style="font-weight:bold;font-size:' . $fontsize . ';">' . $item->quantity . '</td><td>' . number_format($item->price, 2, ',', ' ') . '</td></tr><tr style="font-size:3px"><td> </td></tr>';
}
$items .= '<tr><td colspan="6" style="text-align:right;font-size:16px;font-weight:bold;">Suma: ' . number_format($zatowary, 2, ',', ' ') . ' zł</td></tr>';
$items = '<table>' . $items . '</table>';

$deliveryname = $delivery['methodname'];
$deliveryname .= ($delivery['smart'] != 0) ? ' SMART' : '';

$kosztdostawy = number_format($delivery['cost'], 2, ',', ' ');
if ($delivery['smart'] != 0 & $delivery['cost'] == 0.00) {
	$kosztdostawy = (!is_null($delivery['numberofpackages'])) ? 'Paczek: ' . $delivery['numberofpackages'] : $kosztdostawy;
}

$sposobplatnosci = ($order['paymenttype'] == "CASH_ON_DELIVERY") ? "Pobranie" : "";
$sposobplatnosci .= ($order['paymenttype'] == "ONLINE") ? $order['paymentprovider'] : "";
$sposobplatnosci .= ($order['paymenttype'] == "EXTENDED_TERM") ? "PragmaGO - przelew zwykły" : "";
$dataplatnosci = ($order['paymenttype'] != "CASH_ON_DELIVERY") ? $order['paymentfinished'] : '';

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
// set margins
$pdf->SetMargins(10, 10, 10);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);
// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 20);
// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
	require_once(dirname(__FILE__) . '/lang/eng.php');
	$pdf->setLanguageArray($l);
}
$pdf->setCellPaddings(0);
$pdf->setCellMargins(0);
$pdf->AddPage();

$pdf->SetFont('dejavusans', 'B', 12);
$pdf->MultiCell(63, 6, 'Zamówienie allegro:', 0, 'L', 0, 0, '', '', true);
$pdf->MultiCell(63, 6, $order['transactionid'], 0, 'C', 0, 1, '', '', true);
$pdf->SetFont('dejavusans', '', 10);
$pdf->MultiCell(63, 5, $order['readytime'], 0, 'L', 0, 1, '', '', true);
$pdf->MultiCell(90, 5, $buyer['email'], 0, 'L', 0, 0, '', '', true);
$pdf->MultiCell(80, 5, $order['buyerlogin'], 0, 'L', 0, 1, '', '', true);
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->MultiCell(90, 5, 'Dane bilingowe:', 0, 'L', 0, 0, '', '', true);
$pdf->MultiCell(80, 5, 'Dane dostawy:', 0, 'L', 0, 1, '', '', true);
$x = $pdf->getX();
$y = $pdf->getY();
$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(150, 150, 150));
$pdf->Line($x, $y, $x + 190, $y, $style);
$pdf->SetFont('dejavusans', '', 10);
$x = $pdf->getX();
$y = $pdf->getY();
$pdf->writeHTMLCell(190, 0, $x, $y + 1, $danedw, 0, 1, 0, true, 'L');
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->MultiCell(90, 8, 'Produkty:', 0, 'L', 0, 1, '', '', true, 0, false, true, 10, 'B');
$x = $pdf->getX();
$y = $pdf->getY();
$pdf->Line($x, $y, $x + 190, $y, $style);
$pdf->SetFont('dejavusans', '', 9);
$pdf->writeHTMLCell(190, 0, $x, $y + 1, $items, 0, 1, 0, true, 'L');
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->MultiCell(90, 10, 'Sposób i koszt dostawy:', 0, 'L', 0, 1, '', '', true, 0, false, true, 10, 'B');
$x = $pdf->getX();
$y = $pdf->getY();
$pdf->Line($x, $y, $x + 190, $y, $style);
$pdf->SetFont('dejavusans', '', 10);
$pdf->MultiCell(160, 5, $deliveryname, 0, 'L', 0, 0, '', '', true);
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->MultiCell(30, 5, $kosztdostawy, 0, 'R', 0, 1, '', '', true);
($delivery['pickuppoint'] != NULL & !strpos($delivery['pickuppoint'], 'SAT-SERWIS')) ? $pdf->MultiCell(160, 5, $delivery['pickuppoint'], 0, 'L', 0, 1, '', '', true) : '';
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->MultiCell(90, 10, 'Dane płatności:', 0, 'L', 0, 1, '', '', true, 0, false, true, 10, 'B');
$x = $pdf->getX();
$y = $pdf->getY();
$pdf->Line($x, $y, $x + 190, $y, $style);
$pdf->SetFont('dejavusans', '', 10);
$pdf->MultiCell(140, 5, 'Sposób płatności:', 0, 'L', 0, 0, '', '', true);
$pdf->MultiCell(50, 5, 'Data płatności', 0, 'L', 0, 1, '', '', true);
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->MultiCell(140, 5, $sposobplatnosci, 0, 'L', 0, 0, '', '', true);
$pdf->MultiCell(50, 5, $dataplatnosci, 0, 'L', 0, 1, '', '', true);

$suma = $order['summary'];

if (!empty($surcharges)) {
	$pdf->SetFont('dejavusans', 'B', 11);
	$pdf->MultiCell(90, 10, 'Dopłata:', 0, 'L', 0, 1, '', '', true, 0, false, true, 10, 'B');
	$x = $pdf->getX();
	$y = $pdf->getY();
	$pdf->Line($x, $y, $x + 190, $y, $style);
	$pdf->SetFont('dejavusans', '', 10);
	$pdf->MultiCell(70, 5, 'Sposób płatności:', 0, 'L', 0, 0, '', '', true);
	$pdf->MultiCell(80, 5, 'Data płatności', 0, 'L', 0, 0, '', '', true);
	$pdf->MultiCell(50, 5, 'Kwota dopłaty', 0, 'L', 0, 1, '', '', true);
	$pdf->SetFont('dejavusans', 'B', 11);
	$pdf->MultiCell(70, 5, $surcharges['methodprovider'] . ' id:' . $surcharges['transactionid'], 0, 'L', 0, 0, '', '', true);
	$pdf->MultiCell(80, 5, $surcharges['finishedat'], 0, 'L', 0, 0, '', '', true);
	$pdf->MultiCell(50, 5, number_format($surcharges['price'], 2, ',', ' '), 0, 'L', 0, 1, '', '', true);
	$suma += $surcharges['price'];
}

if ($message != '' & $messageprint == "true") {
	$pdf->MultiCell(90, 10, 'Uwagi do zamówienia:', 0, 'L', 0, 1, '', '', true, 0, false, true, 10, 'B');
	$x = $pdf->getX();
	$y = $pdf->getY();
	$pdf->Line($x, $y, $x + 190, $y, $style);
	$pdf->SetFont('dejavusans', '', 10);
	$pdf->MultiCell(190, 0, $message, 0, 'L', 0, 1, '', '', true);
}
$pdf->SetFont('dejavusans', 'B', 16);
$pdf->MultiCell(190, 20, 'Łączna wartość: ' . number_format($suma, 2, ',', ' '), 0, 'R', 0, 1, '', '', true, 0, false, true, 10, 'B');

if ($czyfaktura == 0) {
	$y = $pdf->getY();
	$styledot = array('width' => 0.5, 'cap' => 'round', 'join' => 'round', 'dash' => '20,40', 'color' => array(150, 150, 150));
	if ($y < 150) {
		$pdf->setY(150);
		$y = $pdf->getY();
		$pdf->Line($x, $y, $x + 190, $y, $styledot);
		$y = $pdf->getY();
		$pdf->setY($y + 2);
	} elseif ($y > 149 & $y < 210) {
		$pdf->setY($y + 5);
		$y = $pdf->getY();
		$pdf->Line($x, $y, $x + 190, $y, $styledot);
		$y = $pdf->getY();
		$pdf->setY($y + 2);
	} else {
		$pdf->AddPage();
	}

	$pdf->SetFont('dejavusans', 'B', 12);
	$pdf->MultiCell(63, 8, 'Zamówienie allegro:', 0, 'L', 0, 0, '', '', true);
	$pdf->MultiCell(63, 8, $order['transactionid'], 0, 'C', 0, 0, '', '', true);
	$pdf->MultiCell(63, 8, $order['readytime'], 0, 'R', 0, 1, '', '', true);
	$pdf->SetFont('dejavusans', '', 10);
	$pdf->MultiCell(90, 5, $buyer['email'], 0, 'L', 0, 0, '', '', true);
	$pdf->MultiCell(80, 5, $order['buyerlogin'], 0, 'L', 0, 1, '', '', true);
	$pdf->SetFont('dejavusans', 'B', 11);
	$pdf->MultiCell(90, 5, 'Dane bilingowe:', 0, 'L', 0, 0, '', '', true);
	$pdf->MultiCell(80, 5, 'Dane dostawy:', 0, 'L', 0, 1, '', '', true);
	$pdf->SetFont('dejavusans', '', 10);
	$x = $pdf->getX();
	$y = $pdf->getY();
	$pdf->writeHTMLCell(190, 0, $x, $y + 1, $danedw, 0, 1, 0, true, 'L');
	$pdf->SetFont('dejavusans', '', 10);
	$pdf->MultiCell(130, 5, $deliveryname, 0, 'L', 0, 1, '', '', true);
	$pdf->SetFont('dejavusans', 'B', 10);
	($delivery['pickuppoint'] != NULL & !strpos($delivery['pickuppoint'], 'SAT-SERWIS')) ? $pdf->MultiCell(190, 5, $delivery['pickuppoint'], 0, 'L', 0, 1, '', '', true) : '';
	$pdf->SetFont('dejavusans', 'B', 11);
	$pdf->MultiCell(63, 5, $sposobplatnosci, 0, 'L', 0, 0, '', '', true);
	$pdf->MultiCell(63, 5, $dataplatnosci, 0, 'C', 0, 0, '', '', true);
	$pdf->MultiCell(63, 5, 'Łączna wartość: ' . number_format($order['summary'], 2, ',', ' '), 0, 'R', 0, 1, '', '', true);
	if (!empty($surcharges)) {
		$pdf->MultiCell(63, 5, 'Dopłata ' . $surcharges['methodprovider'] . ' id:' . $surcharges['transactionid'], 0, 'L', 0, 0, '', '', true);
		$pdf->MultiCell(63, 5, $surcharges['finishedat'], 0, 'C', 0, 0, '', '', true);
		$pdf->MultiCell(63, 5, 'Kwota dopłaty: +' . number_format($surcharges['price'], 2, ',', ' '), 0, 'R', 0, 1, '', '', true);
		$pdf->MultiCell(190, 5, 'Łącznie: ' . number_format($suma, 2, ',', ' '), 0, 'R', 0, 1, '', '', true);
	}
}
$pdf->Output('Allegro-order-' . $order['transactionid'] . '.pdf', 'I');
