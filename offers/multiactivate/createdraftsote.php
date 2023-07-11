<?php
include_once("../allegrofunction.php");

if(!isset($_COOKIE['sklep'])){
    $sesja = new SoapClient('https://sklep.satserwis.pl/backend.php/webapi/soap?wsdl');
    $log = new stdClass();
    $log->username = "webapi@marketpol.pl";
    $log->password = "maciejek1";
    setcookie('sklep',$sesja->doLogin($log)->hash);
}
?>
<form method="post"><textarea name="code" rows="1" cols="20"></textarea><input type="submit"></form>
<pre>
<?php
if(isset($_COOKIE['sklep'])){
$ciacho = $_COOKIE['sklep'];
$code = $_POST['code'];

if(isset($code)){
$product = new SoapClient('https://sklep.satserwis.pl/backend.php/product/soap?wsdl');
$productcode = new stdClass();
$productcode->_session_hash = $ciacho;
$productcode->code = $code;
$dane = $product->GetProductByCode($productcode);
$product = new SoapClient('https://sklep.satserwis.pl/backend.php/product/soap?wsdl');
$productimage = new stdClass();
$productimage->_session_hash = $ciacho;
$productimage->product_id = $id;
$productimage->_offset = 0;
$productimage->_limit = 30;


$draft = new stdClass();
$draft->name = $dane->name;
$draft->external->id = $dane->code;
$draft->payments = "VAT";
$draft->ean = $dane->man_code;
/*$draft->afterSalesServices->impliedWarranty->id = "77fc4534-9ae2-4d40-a0a5-aafdfd8346fb";
$draft->afterSalesServices->returnPolicy->id = "6f5e6c13-ce36-4f66-8473-85d1e93cc718";
$draft->afterSalesServices->warranty->id = "239d4151-c96a-41a7-b2ca-abb68f7aad2b";
$draft->location->countryCode ="PL";
$draft->location->province = "LODZKIE";
$draft->location->city = "Łódź";
$draft->location->postCode = "91-425";
$draft->delivery->handlingTime = "PT24H";*/


//tworzy draft na podstawie aukcji
if(isset($draft)){
    $i = postPublic('https://api.allegro.pl/sale/offers',(array)$draft);
    $i = json_decode($i);
    echo '<a target="_blank" href="https://allegro.pl/offer/'.$i->id.'/restore">'.$i->id.'</a>';
}

/*
//edytuje dane aukcji
if(isset($info)){
    $i = putPublic('https://api.allegro.pl/sale/offers/'.$_POST['offerid'],(array)$info);
    print_r($i);
}*/
}
}
?>