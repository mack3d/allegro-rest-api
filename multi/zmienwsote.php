<?php

if(!isset($_COOKIE['sklep'])){
    $sesja = new SoapClient('https://sklep.satserwis.pl/backend.php/webapi/soap?wsdl');
    $log = new stdClass();
    $log->username = "webapi@marketpol.pl";
    $log->password = "maciejek1";
    setcookie('sklep',$sesja->doLogin($log)->hash,time()+7200);
    header("Location: ./");
}

$stproduct = new SoapClient('https://sklep.satserwis.pl/backend.php/product/soap?wsdl');

$productcount = new stdClass();
$productcount->_session_hash = $_COOKIE['sklep'];
$productcount = $stproduct->CountProduct($productcount);

for($i=0;$i<$productcount->_count;$i++){
    $product = new stdClass();
    $product->_session_hash = $_COOKIE['sklep'];
    $product->_offset = $i;
    $product->_limit = 1;
    $product = $stproduct->GetProductList($product);

    echo '<pre>';
    print_r($product[0]->name);
    print_r($product[0]->stock);

    $productupd = new stdClass();
    $productupd->_session_hash = $_COOKIE['sklep'];
    $productupd->id = $product[0]->id;
    $productupd->stock = 0;
    $stproduct->UpdateProduct($productupd);

    $product = new stdClass();
    $product->_session_hash = $_COOKIE['sklep'];
    $product->_offset = $i;
    $product->_limit = 1;
    $product = $stproduct->GetProductList($product);
    
    print_r($product[0]->stock);
    echo '<br>';
}

?>