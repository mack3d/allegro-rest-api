<?php
include_once("../../allegrofunction.php");

$nazwa = getRequestPublic('https://api.allegro.pl/sale/offers?publication.status=ACTIVE&limit=1');
$ilosc = json_decode($nazwa)->totalCount;
$wszystkie = array();
for($i=0;$i<ceil($ilosc/1000);$i++){
    $aukcje = getRequestPublic('https://api.allegro.pl/sale/offers?publication.status=ACTIVE&limit=1000&offset='.$i*1000);
    $wszystkie = array_merge($wszystkie,json_decode($aukcje)->offers);
}
print_r(json_encode($wszystkie));
/*
$daneoferty = getRequestPublic('https://api.allegro.pl/pricing/offer-quotes?offer.id=9099900912');

print_r(json_decode($daneoferty));
*/

?>