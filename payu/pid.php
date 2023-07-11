<?php
$id = $_POST['pid'];

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

$doGetMyPayoutsDetails_request = array('sessionId' => $session->sessionHandlePart,'payoutId' => $id,'limit' => 300,'offset' => 0);

$wyplata = $soapClient->doGetMyPayoutsDetails($doGetMyPayoutsDetails_request);

$wplaty = $wyplata->payments->item;
$zwroty = (isset($wyplata->refundTo->item))?$wyplata->refundTo->item:array();
$all = array_merge($wplaty,$zwroty);

echo json_encode($all);
?>