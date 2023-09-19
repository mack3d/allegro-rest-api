<?php
include_once("../../../allegrofunction.php");
include_once("../../../../database.class.php");

$pdo = new DBconn();
$allegro = new AllegroServices();

$towary = explode("#", $_POST['towary']);
$ilosc = '';
$odp = array();

function fpp($kod, $pdo)
{
    $ilosc = $pdo->prepare('SELECT kodn,ilosc,nazwa FROM fpp WHERE kodn=:kodn');
    $ilosc->bindValue(":kodn", $kod[0], PDO::PARAM_STR);
    $ilosc->execute();
    if ($ilosc->rowCount() > 0) {
        $d = $ilosc->fetch();
        $ilosc = $d[1];
        $nazwa = $d[2];
    } else {
        $ilosc = "brak danych.";
        $nazwa = "brak towaru";
    }
    return array("kod" => $kod[0], "nazwa" => $nazwa, "ilosc" => $ilosc, "cena" => $kod[1]);
}


/*$nazwa = $allegro->sale("GET", '/offers?limit=1');
$ilosc = $nazwa->totalCount;
$wszystkie = array();
for($i=0;$i<ceil($ilosc/1000);$i++){
    $aukcje = getRequestPublic('https://api.allegro.pl/sale/offers?limit=1000&offset='.$i*1000);
    $wszystkie = array_merge($wszystkie,json_decode($aukcje)->offers);
}*/

function dopasowane($kod, $wszystkie)
{
    $ret = array();
    foreach ($wszystkie as $aukcja) {
        if (isset($aukcja->external->id)) {
            if (strpos(strtoupper($aukcja->external->id), strtoupper($kod)) !== false || strpos(strtoupper($aukcja->name), strtoupper($kod)) !== false) {
                array_push($ret, $aukcja);
            }
        } else {
            if (strpos(strtoupper($aukcja->name), strtoupper($kod)) !== false) {
                array_push($ret, $aukcja);
            }
        }
    }
    return json_decode(json_encode($ret));
}

$wszystkie = getalloffers("ALL");
foreach ($towary as $towar) {
    if ($towar != '') {
        $towar = explode("|", $towar);
        $pozycja = fpp($towar, $pdo);

        $re = dopasowane($towar[0], $wszystkie);
        $pozycja['aukcje'] = $re;
        array_push($odp, $pozycja);
    }
}

print_r(json_encode($odp));
