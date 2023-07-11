<pre><?php
include_once("../../allegrofunction.php");

$offers = array();
$i = 0;
while (true){
    $aukcje = getRequestPublic('https://api.allegro.pl/sale/offers?publication.status=ENDED&limit=1000&offset='.$i*1000);
    $aukcje = json_decode($aukcje);
    if (($i*1000) > $aukcje->totalCount){
        break;
    }
    $offers = array_merge($offers,$aukcje->offers);
    $i++;
}

function ktotozrobil($id){
    $aukcja = getRequestPublic('https://api.allegro.pl/sale/offers/'.$id);
    $aukcja = json_decode($aukcja);
    if ($aukcja->publication->endedBy == "ADMIN"){
        return 1;
    }else{
        return 0;
    }
}

$oferty = array();
foreach ($offers as $offer){
    if($offer->stock->available > 0){
        if (ktotozrobil($offer->id) == 1){
            array_push($oferty,array("id"=>$offer->id));
        }
    }
    if (count($oferty) == 200){
        break;
    }
}
//print_r($oferty);

$uuid = uuid();


$dane = array("publication"=>array("action"=>"ACTIVATE"),"offerCriteria"=>array(array("offers"=>$oferty,"type"=>"CONTAINS_OFFERS")));
$info = putPublic('https://api.allegro.pl/sale/offer-publication-commands/'.$uuid, $dane);

print_r($info);

/*
$url = 'https://api.allegro.pl/sale/offer-publication-commands/'.$uuid;
while (true){
    sleep(1);
    $res = getRequestPublic($url);
    $status = json_decode($res);
    print_r($status);
}
*/


print_r($oferty);
?>