<?php
include_once("../../../allegrofunction.php");

$offset = $_POST['offset'];
$status = $_POST['status'];
$sortby = $_POST['sort'];
$limit = $_POST['limit'];
$cenaod = trim($_POST['cenaod']);
$cenado = trim($_POST['cenado']);
$kod = trim($_POST['kod']);
$offerids = trim($_POST['offerids']);
$kody = explode(" ",$kod);

$offset = $offset*$limit;
$status = ($status != "ALL")?'&publication.status='.$status:'';
$oplatautrzymaniowa = $sortby;
$sort = ($sortby != "default" && $sortby != "-default" && $sortby != "events")?'&sort='.$sortby:'';
$limit = ($limit == '')?1:$limit;
$cenaod = ($cenaod == '')?'':"&sellingMode.price.amount.gte=".$cenaod;
$cenado = ($cenado == '')?'':"&sellingMode.price.amount.lte=".$cenado;
$events = '';

if ($sortby != "events"){
    if ((trim($kod) != "" && strlen($kod) > 3) || $oplatautrzymaniowa == "-default" || $offerids != ""){
        $alloffers = array();
        $szukane = array();
        if ($offerids == ''){
            if ($oplatautrzymaniowa == "-default"){
                $alloffers = getalloffers();
                $alloffers = array_reverse($alloffers);

                for ($o = $offset; count($szukane) < $limit; $o++){
                    array_push($szukane,$alloffers[$o]);
                }
            }else{
                $alloffers = getalloffers("ALL");
                $arkod = explode(" ",trim($kod));
                foreach ($alloffers as $offer){
                    if (isset($offer->external->id)){
                        $extcodes = explode(",",preg_replace('/[^0-9\-]/', ',', $offer->external->id));
                    }else{
                        $extcodes = explode(",",preg_replace('/[^0-9\-]/', ',', substr($offer->name,-5)));
                    }
                    if (count(array_intersect($arkod, $extcodes)) > 0){
                        array_push($szukane,$offer);
                    }
                }
            }
        }else{
            $offerids = explode(' ',$offerids);
            foreach ($offerids as $id){
                $offer = getRequestPublic('https://api.allegro.pl/sale/offers?offer.id='.$id);
                $offer = json_decode($offer);
                array_push($szukane,$offer->offers[0]);
            }
        }
        
        $i = json_decode(json_encode(array('offers' => $szukane, 'totalCount' => count($alloffers), 'count' => count($szukane))));
    }else{
        $i = getRequestPublic('https://api.allegro.pl/sale/offers?limit='.$limit.'&offset='.$offset.$sort.$status.$cenado.$cenaod);
        $i = json_decode($i);
    }
}else{
    $events = getRequestPublic('https://api.allegro.pl/sale/offer-events?type=OFFER_STOCK_CHANGED&limit=1000');
    $events = json_decode($events);
    $offerslist = array();
    $checkids = array(); //sprawdza czy aukcje o podanym id już jest na liście

    $events = array_reverse($events->offerEvents);
    for ($e = $offset; $e < count($events); $e++){
        $event = $events[$e];
        $offer = getRequestPublic('https://api.allegro.pl/sale/offers?offer.id='.$event->offer->id);
        $offer = json_decode($offer);
        if ($offer->count != 0){ //aukccja moze być ukryta i wtedy jej nie można pobrać
            if (!in_array($event->offer->id,$checkids)){ //sprawza czy nie ma już aukcji o podanym id na liście - nie checmy dubli
                array_push($checkids,$offer->offers[0]->id);
                array_push($offerslist,$offer->offers[0]);
            }
        }
        if (count($checkids) >= $limit){
            break;
        }
    }
    $offers = array("offers" => $offerslist, "count" => count($offerslist), "totalCount" => count($events));
    $i = json_decode(json_encode($offers));
}

$pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4','root','');$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dane = $pdo->prepare('SELECT nazwa,kodn,cena,ilosc FROM fpp WHERE FIND_IN_SET(kodn, :kod) ORDER BY cena DESC');

function likecodes($lista){
    $re = array();
    foreach ($lista as $kod){
        array_push($re,$kod);
        if (strlen($kod) > 4){
            array_push($re,substr($kod,0,4));
        }
    }
    return $re;
}

$codes_list = array();
foreach($i->offers as $offer){
    if (isset($offer->external)){
        $extcodes = explode(",",preg_replace('/[^0-9\-]/', ',', $offer->external->id));
        $extcodes = likecodes($extcodes);
        $codes_list = array_merge($codes_list, $extcodes);
    }else{
        $extcodes = explode(",",preg_replace('/[^0-9\-]/', ',', substr($offer->name,-5)));
        $extcodes = likecodes($extcodes);
        $codes_list = array_merge($codes_list, $extcodes);
    }
}
$codes_list = array_merge($codes_list, $kody);
$codes_list = array_unique($codes_list);

$dane->bindValue(":kod", implode(',',$codes_list), PDO::PARAM_STR);
$dane->execute();
$fpp = array();
$fpp2 = array();
if ($dane->rowCount()>0){
    foreach($dane->fetchAll() as $d){
        array_push($fpp,json_decode(json_encode($d)));
        $fpp2[$d['kodn']] = json_decode(json_encode($d));
    }
    $fpp2['count'] = count($fpp2);
}else{
    array_push($fpp,json_decode(json_encode(array("count"=>0))));
    $fpp2['count'] = 0;
}

$resp = array();
//$resp['codes_list'] = json_decode(json_encode($codes_list));
$resp['shipping'] = getShipping();
$resp['aukcje'] = $i;
$resp['fpp2'] = json_decode(json_encode($fpp2));
if (gettype($events) == "array")$resp['events'] = $events;

print_r(json_encode($resp));

?>