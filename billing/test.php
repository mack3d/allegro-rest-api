<?php
include_once("../allegrofunction.php");
require_once('../../tcpdf/tcpdf.php');
include_once("../../database.class.php");
$pdo = new DBconn();
$allegro = new allegroServices();

function cmpw($a, $b)
{
    return strnatcmp($a->occurredAt, $b->occurredAt);
}

function cmpwkasa($a, $b)
{
    return strnatcmp($a->value->amount, $b->value->amount);
}

function trid($payid)
{
    global $pdo;
    $trid = '';
    $transactionid = $pdo->prepare('SELECT transactionid FROM newallegroorders WHERE paymentid=?');
    $transactionid->execute(array($payid));
    if ($transactionid->rowCount() > 0) {
        $trid = $transactionid->fetch()['transactionid'];
    } else {
        $surchargesid = $pdo->prepare('SELECT transactionid FROM newallegrosurcharges WHERE id=?');
        $surchargesid->execute(array($payid));
        $trid = ($surchargesid->rowCount() > 0) ? $surchargesid->fetch()['transactionid'] : '';
    }
    return $trid;
}

function dokument($trid)
{
    global $pdo;
    $dok = $pdo->prepare('SELECT document FROM logistyka_wyslane WHERE orders=? ORDER BY dzien DESC LIMIT 1');
    $dok->execute(array($trid));
    return ($dok->rowCount() > 0) ? $dok->fetch()['document'] : '';
}

$numer = $_GET['numer'];
$data = $_GET['data'];
$suma = $_GET['suma'];
$operator = $_GET['operator'];
$wplaty = array();
$wyplaty = array();
$licznik = 0;

$lte = '&occurredAt.lte=' . $data;
$gte = '&occurredAt.gte=' . date('Y-m-d\T\00:\00:\00.\0\0\0\Z', strtotime("-7 day", strtotime($data)));
$paymentOperator = ($operator != "ALL") ? "&wallet.paymentOperator=" . $operator : '';

$payment = array();
$limit = 100;
$i = 0;
while (true) {
    $offset = $limit * $i;
    $pay = $allegro->payments("GET", '/payment-operations?limit=' . $limit . '&offset=' . $offset . $gte . $lte . $paymentOperator);
    $payment = array_merge($payment, $pay->paymentOperations);
    if ($offset > $pay->totalCount) {
        break;
    }
    $i++;
}

usort($payment, "cmpw");
krsort($payment);

$artest = array();

array_shift($payment);
/*
echo '<pre>';
print_r($payment);*/

foreach ($payment as $incom) {
    /*echo '<br>';
    echo $incom->group;*/
    if ($incom->group != "OUTCOME") {
        $licznik += $incom->value->amount;

        if ($incom->group == 'INCOME') {
            array_push($wplaty, $incom);
        }

        if ($incom->group == 'REFUND') {
            array_push($wyplaty, $incom);
        }
        array_push($artest, $incom);
    } else {
        break;
    }
}
/*
foreach($artest as $at){
    echo '<br>';
    echo $at->group;
}*/



usort($wplaty, "cmpwkasa");
krsort($wplaty);

$k = 0;
$tw = '<table><tr style="font-weight:bold; font-size:10;"><td width="100" height="25">Transakcja</td><td width="90">Dokument</td><td width="200">Wpłacający</td><td width="110">Login</td><td width="80">Wpłata</td><td width="90">Data</td></tr>';
foreach ($wplaty as $wplata) {
    if ($k == 1) {
        $kolor = '#fff';
        $k = 0;
    } else {
        $kolor = '#f2f2f2';
        $k = 1;
    }
    $trid = trid($wplata->payment->id);
    $wplacajacy = ($wplata->participant->companyName != '') ? $wplata->participant->companyName . ' ' : '';
    $wplacajacy .= ($wplata->participant->firstName != '') ? $wplata->participant->firstName : '';
    $wplacajacy .= ($wplata->participant->lastName != '') ? ' ' . $wplata->participant->lastName : '';
    if (isset($wplata->surcharge->id)) {
        $trid = trid($wplata->surcharge->id);
    }
    $tw .= '<tr style="background-color:' . $kolor . ';"><td valign="middle" style="line-height: 2;">' . $trid . '</td><td valign="middle" style="line-height: 2;">' . dokument($trid) . '</td><td valign="middle" style="line-height: 2; font-size: 10;">' . $wplacajacy . '</td><td valign="middle" style="line-height: 2; font-size: 9;">' . $wplata->participant->login . '</td><td align="center" style="line-height: 2;">' . number_format($wplata->value->amount, 2, ",", " ") . '</td><td style="line-height: 2;">' . date('Y-m-d', strtotime($wplata->occurredAt)) . '</td></tr>';
}

if (!empty($wyplaty)) {
    usort($wyplaty, "cmpwkasa");
    krsort($wyplaty);

    $k = 0;
    $tw .= '<tr style="font-weight:bold; font-size:14;"><td colspan="6">ZWROTY (' . count($wyplaty) . ')</td></tr>';
    foreach ($wyplaty as $wplata) {
        if ($k == 1) {
            $kolor = '#fff';
            $k = 0;
        } else {
            $kolor = '#f2f2f2';
            $k = 1;
        }
        $trid = trid($wplata->payment->id);
        $tw .= '<tr style="background-color:' . $kolor . ';"><td width="100" valign="middle" style="line-height: 2;">' . $trid . '</td><td width="90" valign="middle" style="line-height: 2;"> ' . dokument($trid) . '</td><td width="200" valign="middle" style="line-height: 2; font-size: 10;">' . $wplata->participant->firstName . ' ' . $wplata->participant->lastName . '</td><td width="110" valign="middle" style="line-height: 2; font-size: 10;">' . $wplata->participant->login . '</td><td width="80" align="center" style="line-height: 2;">' . number_format($wplata->value->amount, 2, ",", " ") . '</td><td width="90" style="line-height: 2;">' . date('Y-m-d', strtotime($wplata->occurredAt)) . '</td></tr>';
    }
}
$tw .= '</table>';

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
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}
$pdf->setCellPaddings(0);
$pdf->setCellMargins(0);

$pdf->AddPage();
$pdf->SetFont('dejavusans', 'B', 13);
$pdf->MultiCell('', 8, 'Zestawienie transakcji ' . $operator, 0, 'L', 0, 1, 90, '', true);
$pdf->SetFont('dejavusans', '', 10);
$pdf->MultiCell('', 5, 'Wyplata nr. ' . $numer, 0, 'L', 0, 1, 90, '', true);
$pdf->SetFont('dejavusans', '', 10);
$pdf->MultiCell('', 5, 'Data wypłaty: ' . date('Y-m-d', strtotime($data)), 0, 'L', 0, 1, 90, '', true);
$pdf->MultiCell('', 10, 'Kwota: ' . number_format($licznik, 2, ",", " ") . ' zł', 0, 'L', 0, 1, 90, '', true);

$pdf->writeHTML($tw, true, false, false, false, '');

$pdf->Output('Allegro-payu-' . $numer . '.pdf', 'I');
