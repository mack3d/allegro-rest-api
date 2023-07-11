<?php
require_once('../../tcpdf/tcpdf.php');
include_once("../allegrofunction.php");

$pdo = new PDO('mysql:host=localhost;dbname=satserwis','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$fod = $pdo->prepare('SELECT fod,transactionid,readytime,paymentpaid FROM newallegroorders WHERE paymentid=:paymentid');

$numery=$_GET['cb'];

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(10, 10, 10);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}
$pdf->setCellPaddings(0);
$pdf->setCellMargins(0);

$pdf->AddPage();
$tpage = 0;
foreach($numery as $numer){
	$payment = getRequestPublic('https://api.allegro.pl/payments/payment-operations?payment.id='.$numer);
	$payment = json_decode($payment);
	$fod->bindValue(':paymentid', $numer, PDO::PARAM_STR);
    $fod->execute();
	$e = $fod->fetch(PDO::FETCH_ASSOC);
	$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(150,150,150));
	
	foreach($payment->paymentOperations as $operacja){
		if($operacja->group == "REFUND"){
			if($tpage>4){$pdf->AddPage();$tpage=0;}
			$operator = ($operacja->wallet->paymentOperator=="P24")?"Przelewy24":"PayU";
			$participant = ($operacja->participant->companyName!='')?$operacja->participant->companyName.' ':'';
			$participant .= $operacja->participant->firstName.' '.$operacja->participant->lastName.' '.$operacja->participant->address->street.' '.$operacja->participant->address->postCode.' '.$operacja->participant->address->city;
			$html = '<table><tr><td colspan="3" style="font-size: 16px;">Zwrot dla <b>'.$operacja->participant->login.'</b></td></tr>
			<tr><td colspan="3" style="font-size: 12px;">'.$participant.'</td></tr>
			<tr><td colspan="3" style="font-size: 12px; font-weight:bold;">Dane zwrotu:</td></tr>
			<tr><td>Kwota: '.$operacja->value->amount.'</td><td>Data: '.date("Y-m-d",strtotime($operacja->occurredAt)).'</td><td>'.$operator.'</td></tr>
			<tr><td colspan="3" style="color:#888;">Id zwrotu: '.$operacja->payment->id.'</td></tr>
			<tr><td></td></tr>
			<tr><td style="font-size: 12px; font-weight:bold;">Wplata: '.$e['readytime'].'</td><td>Kwota: '.number_format($e['paymentpaid'],2,',',' ').'</td><td>Transakcja nr: '.$e['transactionid'].'</td></tr>
			<tr><td colspan="2" style="color:#888;">ID: '.$e['fod'].'</td></tr>
			</table>';
			$pdf->writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='');
			$x = $pdf->getX();
			$y = $pdf->getY();
			$pdf->Line($x, $y, $x+190, $y, $style);
			$y = $pdf->getY();
			$pdf->setY($y+5);
			$tpage++;
		}
	}
}

$pdf->Output('Allegro-payu-zwroty.pdf', 'I');