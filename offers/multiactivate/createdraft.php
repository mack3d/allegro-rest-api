<?php
include_once("../allegrofunction.php");

$info = getRequestPublic('https://api.allegro.pl/sale/offers/9200591523');
$info = json_decode($info);

/*
if(!isset($_COOKIE['sklep'])){
    $sesja = new SoapClient('https://sklep.satserwis.pl/backend.php/webapi/soap?wsdl');
    $log = new stdClass();
    $log->username = "webapi@marketpol.pl";
    $log->password = "maciejek1";
    setcookie('sklep',$sesja->doLogin($log)->hash);
}
?>
<form method="post"><textarea name="offerid" rows="1" cols="20"></textarea><input type="submit"></form>
<pre>
<?php

if(isset($_POST['offerid'])){
    $info = getRequestPublic('https://api.allegro.pl/sale/offers/'.$_POST['offerid']);
    $info = json_decode($info);
    unset($info->id);
    unset($info->compatibilityList);
    unset($info->tecdocSpecification);
    unset($info->publication);
    unset($info->additionalServices);
    unset($info->sizeTable);
    unset($info->promotion);
    unset($info->validation);
    unset($info->createdAt);
    unset($info->updatedAt);
    print_r($info);
}

/*
//tworzy draft na podstawie aukcji
if(isset($info)){
    $i = postPublic('https://api.allegro.pl/sale/offers',(array)$info);
    $i = json_decode($i);
    echo '<a target="_blank" href="https://allegro.pl/offer/'.$i->id.'/restore">'.$i->id.'</a>';
}

/*
//edytuje dane aukcji
if(isset($info)){
    $i = putPublic('https://api.allegro.pl/sale/offers/'.$_POST['offerid'],(array)$info);
    print_r($i);
}*/
?>