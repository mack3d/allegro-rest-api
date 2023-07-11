<?php
//$kod = $_POST['kod'];
$kod = "2520";

include_once("../allegrofunction.php");

$nazwa = getRequestPublic('https://api.allegro.pl/sale/offers?publication.status=ACTIVE&limit=1');
$ilosc = json_decode($nazwa)->totalCount;
$wszystkie = array();
for($i=0;$i<ceil($ilosc/1000);$i++){
    $aukcje = getRequestPublic('https://api.allegro.pl/sale/offers?publication.status=ACTIVE&limit=1000&offset='.$i*1000);
    $wszystkie = array_merge($wszystkie,json_decode($aukcje)->offers);
}

$ret = array();

foreach($wszystkie as $aukcja){
    if(isset($aukcja->external->id)){
        if(strpos(strtoupper($aukcja->external->id),strtoupper($kod)) or strpos(strtoupper($aukcja->name),strtoupper($kod))){
            array_push($ret,$aukcja);
        }
    }else{
        if(strpos(strtoupper($aukcja->name),strtoupper($kod))){
            array_push($ret,$aukcja);
        }
    }
}

print_r(json_encode($ret));
?>