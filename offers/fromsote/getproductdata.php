<?php
$code = $_POST['code'];
function getProductData($code){
    $product = new SoapClient('https://sklep.satserwis.pl/backend.php/product/soap?wsdl');
    $productcode = new stdClass();
    $productcode->_session_hash = $_COOKIE['sklep'];
    $productcode->code = strval($code);
    return $product->GetProductByCode($productcode);
}

$pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4','root','');$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dane = $pdo->prepare('SELECT * FROM fpp WHERE kodn=:kod');
$dane->bindValue(":kod", $code, PDO::PARAM_STR);
$dane->execute();
$resp = array();
if ($dane->rowCount()>0){
    foreach($dane->fetchAll() as $d){
        array_push($resp,json_decode(json_encode($d)));
    }
}else{
    array_push($resp,json_decode(json_encode(array("echo"=>"Brak wyników"))));
}

$dane = $pdo->prepare('SELECT offername FROM newallegrolineitems WHERE offerexternal=:kod ORDER BY boughtat DESC LIMIT 1');
$dane->bindValue(":kod", $code, PDO::PARAM_STR);
$dane->execute();
$allegro = array();
if ($dane->rowCount()>0){
    foreach($dane->fetchAll() as $d){
        array_push($allegro,json_decode(json_encode(array("lastname"=>$d['offername']))));
    }
}else{
    array_push($allegro,json_decode(json_encode(array("lastname"=>""))));
}

$d = new stdClass();
$d->sote = getProductData($code);
$d->fpp = $resp;
$d->allegro = $allegro;
print_r(json_encode($d));
?>