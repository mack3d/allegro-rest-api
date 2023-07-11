<?php
require_once('../../tcpdf/tcpdf.php');

$pdo = new PDO('mysql:host=localhost;dbname=satserwis','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$nrwyplaty=$_GET['numer'];
$data=$_GET['data'];
$suma=$_GET['suma'];

$options['features'] = SOAP_SINGLE_ELEMENT_ARRAYS;
try{
	$soapClient = new SoapClient('https://webapi.allegro.pl/service.php?wsdl', $options);
	$request = array('countryId' => 1,'webapiKey' => 'c77d0744d4');
	$result = $soapClient->doQueryAllSysStatus($request);
	$versionKeys = array();
	foreach ($result->sysCountryStatus->item as $row) {$versionKeys[$row->countryId] = $row;}
	$request = array('userLogin' => 'isat','userHashPassword' => base64_encode(hash('sha256', 'Radek72335!', true)),'countryCode' => 1,'webapiKey' => 'c77d0744d4','localVersion' => $versionKeys[1]->verKey,);
	$session = $soapClient->doLoginEnc($request);
}catch(Exception $e){echo $e;}

$login = $pdo->prepare('SELECT newallegroorders.buyerlogin AS buyerlogin FROM newallegroorders LEFT JOIN newallegrobuyer USING(fod) WHERE newallegrobuyer.userid=:userid');
$document = $pdo->prepare('SELECT document FROM logistyka_wyslane WHERE orders=:orders');
$nazwa = $pdo->prepare('SELECT newallegrobuyer.username AS nazwa FROM newallegrobuyer LEFT JOIN newallegroorders USING(fod) WHERE newallegroorders.transactionid=:transactionid');

$doGetMyPayoutsDetails_request = array('sessionId' => $session->sessionHandlePart,'payoutId' => $nrwyplaty,'limit' => 300,'offset' => 0);
$wyplata = $soapClient->doGetMyPayoutsDetails($doGetMyPayoutsDetails_request);
(isset($wyplata->payments->item))?$wplaty = $wyplata->payments->item:header('Location: ./');
$tw = '<table><tr style="font-weight:bold; font-size:10;"><td width="90" height="25">Transakcja</td><td width="80">Dokument</td><td width="150">Wpłacający</td><td width="90">Login</td><td width="65">Za towar</td><td width="70">Transport</td><td width="65">Razem</td><td width="80">Data</td></tr>';$tz = '<table>';
function cmpw($a, $b){
    return strnatcmp($a->totalAmount, $b->totalAmount);
}

usort($wplaty, "cmpw");
krsort($wplaty);
$k=0;
foreach($wplaty as $w){
	if($k==1){
        $kolor = '#fff';$k=0;}
    else{
        $kolor = '#f2f2f2';$k=1;}
	$login->bindValue(":userid", $w->userId, PDO::PARAM_STR);
	$login->execute();
	$log = ($login->rowCount()>0)?$login->fetch()['buyerlogin']:'';
	$document->bindValue(":orders", $w->tranasctionId, PDO::PARAM_STR);
	$document->execute();
	$doc = ($document->rowCount()>0)?$document->fetch()['document']:'';
	$nam = $w->userName;
	if(trim($w->userName)==''){
		$nazwa->bindValue(":transactionid", $w->tranasctionId, PDO::PARAM_STR);
		$nazwa->execute();
		$nam = ($nazwa->rowCount()>0)?$nazwa->fetch()['nazwa']:'';
	}

	$tw .= '<tr style="background-color:'.$kolor.';"><td valign="middle" style="line-height: 2;">'.$w->tranasctionId.'</td>
	<td valign="middle" style="line-height: 2;">'.$doc.'</td>
	<td valign="middle" style="line-height: 2;">'.$nam.'</td>
	<td valign="middle" style="line-height: 2;font-size: 10px;">'.$log.'</td>
	<td align="center" style="line-height: 2;">'.number_format($w->amount,2,","," ").'</td>
	<td align="center" style="line-height: 2;">'.number_format($w->transportAmount,2,","," ").'</td>
	<td style="line-height: 2;">'.number_format($w->totalAmount,2,","," ").'</td>
	<td style="line-height: 2;">'.date('Y-m-d',strtotime($w->paidDate)).'</td></tr>';
}
$tw .= '</table>';
$tz = '';
if(isset($wyplata->refundTo->item)){
	$zwroty = $wyplata->refundTo->item;
	function cmpz($a, $b){
		return strnatcmp($a->amount, $b->amount);
	}
	usort($zwroty, "cmpz");
	krsort($zwroty);
	$tz = '<table><tr style="font-weight:bold; font-size:10;"><td colspan="6">ZWROTY</td></tr><tr style="font-weight:bold; font-size:10;"><td width="90" height="25">Transakcja</td><td width="90">ID zwrotu</td><td width="240">Nazwa</td><td width="110">Login</td><td width="70">Kwota</td><td width="90">Data</td></tr>';
	$k=0;
	foreach($zwroty as $z){
		if($k==1){
			$kolor = '#fff';$k=0;}
		else{
			$kolor = '#f2f2f2';$k=1;}
		$login->bindValue(":userid", $z->toUserId, PDO::PARAM_STR);
		$login->execute();
		$log = ($login->rowCount()>0)?$login->fetch()['buyerlogin']:'';
		$nazwa->bindValue(":transactionid", $z->tranasctionId, PDO::PARAM_STR);
		$nazwa->execute();
		$nam = ($nazwa->rowCount()>0)?$nazwa->fetch()['nazwa']:'';
		$tz.='<tr style="background-color:'.$kolor.';"><td valign="middle" style="line-height: 2;">'.$z->tranasctionId.'</td><td valign="middle" style="line-height: 2;">'.$z->refundId.'</td><td valign="middle" style="line-height: 2;">'.$nam.'</td><td valign="middle" style="line-height: 2;">'.$log.'</td><td valign="middle" style="line-height: 2;">-'.$z->amount.'</td><td valign="middle" style="line-height: 2;">'.date('Y-m-d',strtotime($z->paidDate)).'</td></tr>';
	}
	$tz.='</table>';
}
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
$pdf->SetFont('dejavusans', 'B', 13);
$pdf->MultiCell(190, 10, 'Zestawienie transakcji dla wypłaty nr '.$nrwyplaty, 0, 'R', 0, 1, '', '', true);
$pdf->SetFont('dejavusans', '', 10);
$pdf->MultiCell('', 5, 'Data wypłaty: '.date('Y-m-d H:i:s',strtotime($data)), 0, 'L', 0, 1, 130, '', true);
$pdf->MultiCell('', 10, 'Kwota: '.number_format($suma,2,","," ").' zł', 0, 'L', 0, 1, 130, '', true);

$pdf->writeHTML($tw, true, false, false, false, '');

if($tz!=''){
	$pdf->writeHTML($tz, true, false, false, false, '');
}

$pdf->Output('Allegro-payu-'.$nrwyplaty.'.pdf', 'I');