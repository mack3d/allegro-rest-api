<?php
require_once('../../tcpdf/tcpdf.php');

include_once("../allegrofunction.php");

include_once("./returnsInfo.php");

$fod = (isset($_GET['fod'])) ? $_GET['fod'] : $_POST['fod'];

$returns = getRequest('https://api.allegro.pl/order/customer-returns?orderId=' . $fod);
$returns = json_decode($returns);

$returnsInfo = new Returns($returns->customerReturns[0]->parcels[0]);
/*
echo '<pre>';
print_r($returns);
print_r($returnsInfo);
*/
$pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function getOfferExternal($pdo, $fod_id, $item_id)
{
	$ex_code = $pdo->prepare('SELECT offerexternal FROM newallegrolineitems WHERE fod=:fod and offerid=:item_id LIMIT 1');
	$ex_code->bindValue(":fod", $fod_id, PDO::PARAM_STR);
	$ex_code->bindValue(":item_id", $item_id, PDO::PARAM_STR);
	$ex_code->execute();
	return $ex_code->fetch()['offerexternal'];
}

$order = $pdo->prepare('SELECT * FROM newallegroorders WHERE fod=:fod');
$order->bindValue(":fod", $fod, PDO::PARAM_STR);
$order->execute();
$order = $order->fetch();

$buyer = $pdo->prepare('SELECT * FROM newallegrobuyer WHERE fod=:fod');
$buyer->bindValue(":fod", $fod, PDO::PARAM_STR);
$buyer->execute();
$buyer = $buyer->fetch();

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

$deliveryname = $delivery['methodname'];
$deliveryname .= ($delivery['smart'] != 0) ? ' SMART' : '';

$smart = ($delivery['smart'] != 0) ? 'Nie zwracaj kosztów dostawy - zwrot w ramach SMART' : '';

$kosztdostawy = number_format($delivery['cost'], 2, ',', ' ');
if ($delivery['smart'] != 0) {
	if (strpos(strtolower($delivery['methodname']), 'inpost')) {
		$kosztdostawy = (!is_null($delivery['numberofpackages'])) ? 'Paczek: ' . $delivery['numberofpackages'] : $kosztdostawy;
	}
}

$lp = 0;
$zatowary = 0;
$items = '<table><tr style="font-weight:bold;"><td width="22">Lp</td><td width="100">Id</td><td width="458">Nazwa</td><td width="40">Ilość</td><td width="50">Cena</td></tr>';
$customerReturns = $returns->customerReturns[0];
foreach ($customerReturns->items as $item) {
	$zatowary += $item->price->amount * $item->quantity;
	$fontsize = ($item->quantity > 1) ? "14px" : "12px";
	$powod = '';
	if ($item->reason->type == "NONE") {
		$powod = 'odstąpienie bez podania przyczyny';
	} else if ($item->reason->type == "DAMAGED") {
		$powod = ' produkt uszkodzony';
	} else if ($item->reason->type == "NOT_AS_DESCRIBED") {
		$powod = ' niezgodny z opisem';
	} else if ($item->reason->type == "MISTAKE") {
		$powod = ' zakup przez pomyłkę';
	} else if ($item->reason->type == "OVERDUE_DELIVERY") {
		$powod = ' nie dotarł na czas';
	} else if ($item->reason->type == "HIDDEN_FLAW") {
		$powod = ' wada wykryta podczas używania';
	} else if ($item->reason->type == "TRANSPORT") {
		$powod = ' produkt i paczka uszkodzona (w transporcie)';
	} else if ($item->reason->type == "DIFFERENT") {
		$powod = ' towar inny niż zamówiony';
	} else if ($item->reason->type == "DONT_LIKE_IT") {
		$powod = 'nie pasuje mi';
	}
	$powod .= ($item->reason->userComment != '') ? ' (' . $item->reason->userComment . ')' : '';
	$ex_code = getOfferExternal($pdo, $fod, $item->offerId);
	$items .= '<tr><td>' . ++$lp . '</td><td>(' . $item->offerId . ')</td><td>' . $item->name . ' [' . $ex_code . ']</td><td style="font-weight:bold;font-size:' . $fontsize . ';">' . $item->quantity . '</td><td>' . number_format($item->price->amount, 2, ',', ' ') . '</td></tr><tr style="font-size:3px"><td> </td></tr>';
	$items .= '<tr><td></td><td></td><td>Powód zwrotu: ' . $powod . '</td><td></td></tr><tr style="font-size:3px"><td> </td></tr>';
}
$items .= '<tr><td colspan="6" style="text-align:right;font-size:18px;font-weight:bold;">' . number_format($zatowary, 2, ',', ' ') . ' zł</td></tr>';
$items = '<table>' . $items . '</table>';

$sposobplatnosci = ($order['paymenttype'] == "CASH_ON_DELIVERY") ? "Przelew na konto" : "";
$sposobplatnosci .= ($order['paymenttype'] == "ONLINE") ? 'Możesz zwrócić środki poprzez allegro, kupujący płacił przez ' . $order['paymentprovider'] : "";
$dataplatnosci = ($order['paymenttype'] != "CASH_ON_DELIVERY") ? $order['paymentfinished'] : '';

function mon($nr)
{
	$nr = str_split($nr, 2);
	$m = preg_replace(
		array("/01/", "/02/", "/03/", "/04/", "/05/", "/06/", "/07/", "/08/", "/09/", "/10/", "/11/", "/12/"),
		array("stycznia", "lutego", "marca", "kwietnia", "maja", "czerwca", "lipca", "sierpnia", "września", "października", "listopada", "grudnia"),
		$nr[1]
	);
	return $nr[0] . ' ' . $m . ' 20' . $nr[2];
}

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
/*CAŁA STRONA MA 189*/
$pdf->MultiCell(139, 6, 'Zwrot ' . $customerReturns->referenceNumber . ' do transakcji allegro nr: ' . $order['transactionid'], 0, 'L', 0, 0, '', '', true);
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->MultiCell(50, 6, mon(date("dmy", strtotime($customerReturns->createdAt))), 0, 'R', 0, 1, '', '', true);
$pdf->SetFont('dejavusans', '', 10);

if ($returnsInfo->status == 'delivered') {
	$pdf->MultiCell(63, 6, "Dane zakupowe:", 0, 'L', 0, 0, '', '', true);
	$pdf->MultiCell(126, 6, 'doręczono: ' . $returnsInfo->datetime, 0, 'R', 0, 1, '', '', true);
} else {
	$pdf->MultiCell(63, 6, "Dane zakupowe:", 0, 'L', 0, 1, '', '', true);
}

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
$pdf->MultiCell(90, 8, 'Zwracane towary:', 0, 'L', 0, 1, '', '', true, 0, false, true, 10, 'B');
$x = $pdf->getX();
$y = $pdf->getY();
$pdf->Line($x, $y, $x + 190, $y, $style);
$pdf->SetFont('dejavusans', '', 9);
$pdf->writeHTMLCell(190, 0, $x, $y + 1, $items, 0, 1, 0, true, 'L');
if ($smart != '') {
	$pdf->SetFont('dejavusans', 'B', 14);
	$pdf->MultiCell(189, 5, $smart, 0, 'L', 0, 1, '', '', true);
}
$pdf->SetFont('dejavusans', 'B', 12);
$pdf->MultiCell(63, 5, 'Sposób zwrotu:', 0, 'L', 0, 1, '', '', true);

$pdf->SetFont('dejavusans', 'B', 10);
$pdf->MultiCell(189, 7, $sposobplatnosci, 0, 'L', 0, 1, '', '', true);
$pdf->SetFont('dejavusans', '', 10);
if ($order['paymenttype'] != "CASH_ON_DELIVERY") {
	$pdf->MultiCell(189, 7, 'W razie braku środków poniżej są dane do przelewu:', 0, 'L', 0, 1, '', '', true);
}
$pdf->MultiCell(189, 5, $customerReturns->refund->bankAccount->accountNumber, 0, 'L', 0, 1, '', '', true);
$pdf->MultiCell(189, 5, $customerReturns->refund->bankAccount->owner, 0, 'L', 0, 1, '', '', true);
$pdf->MultiCell(189, 5, @$customerReturns->refund->bankAccount->address->street, 0, 'L', 0, 1, '', '', true);
$pdf->MultiCell(189, 5, @$customerReturns->refund->bankAccount->address->postCode . ' ' . @$customerReturns->refund->bankAccount->address->city, 0, 'L', 0, 1, '', '', true);


$pdf->Output('Allegro-zwrot-' . $order['transactionid'] . '.pdf', 'I');
