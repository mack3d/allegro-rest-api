<?php
$kod = $_POST['kod'];

if(!isset($_COOKIE['isat'])){
    $sesja = new SoapClient('https://isat.com.pl/backend.php/webapi/soap?wsdl');
    $log = new stdClass();
    $log->username = "webapi@marketpol.pl";
    $log->password = "maciejek1";
    setcookie('sklep',$sesja->doLogin($log)->hash,time()+7200);
    header("Location: ./");
}
$stproduct = new SoapClient('https://isat.com.pl/backend.php/product/soap?wsdl');
$productbycode = new stdClass();
$productbycode->_session_hash = $_COOKIE['sklep'];
$productbycode->code = $kod;
$product = $stproduct->GetProductByCode($productbycode);
unset($product->short_description);
unset($product->description);

print_r(json_encode($product));
?>