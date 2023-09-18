<?php
$code = $_GET['code'];

$sesja = new SoapClient('https://sklep.satserwis.pl/backend.php/webapi/soap?wsdl');
$log = new stdClass();
$log->username = getenv('SOU');
$log->password = getenv('SOP');
$sesja = $sesja->doLogin($log)->hash;

$product = new SoapClient('https://sklep.satserwis.pl/backend.php/product/soap?wsdl');
$productcode = new stdClass();
$productcode->_session_hash = $sesja;
$productcode->code = strval($code);

print_r(json_encode($product->GetProductByCode($productcode)));
