<?php
include_once("../allegrofunction.php");

if (!isset($_COOKIE['sklep'])) {
    $sesja = new SoapClient('https://sklep.satserwis.pl/backend.php/webapi/soap?wsdl');
    $log = new stdClass();
    $log->username = getenv('SOU');
    $log->password = getenv('SOP');
    setcookie('sklep', $sesja->doLogin($log)->hash);
}
?>
<form method="post"><textarea name="code" rows="1" cols="20"></textarea><input type="submit"></form>
<pre>
<?php
if (isset($_COOKIE['sklep'])) {
    $ciacho = $_COOKIE['sklep'];
    $code = $_POST['code'];

    if (isset($code)) {
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


        //tworzy draft na podstawie aukcji
        if (isset($draft)) {
            $i = postPublic('https://api.allegro.pl/sale/offers', (array)$draft);
            $i = json_decode($i);
            echo '<a target="_blank" href="https://allegro.pl/offer/' . $i->id . '/restore">' . $i->id . '</a>';
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