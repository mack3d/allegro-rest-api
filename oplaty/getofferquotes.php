<pre>
<?php
include_once("../allegrofunction.php");

function wyswietl($offer, $quotes){
    print_r($offer->offers[0]->name);
}

$ao = getalloffers();

$offer = getRequestPublic('https://api.allegro.pl/sale/offers?offer.id=10225784303');
$offer = json_decode($offer);
$quotes = getRequestPublic('https://api.allegro.pl/pricing/offer-quotes?offer.id=10225784303');
foreach (json_decode($quotes)->quotes as $quote){
    if ($quote->type == "INEFFECTIVE_LISTING_FEE"){
        wyswietl($offer, $quotes);
        break;
    }
}
/*
foreach ($ao as $offer){
    $quotes = getRequestPublic('https://api.allegro.pl/pricing/offer-quotes?offer.id='.$offer->id);
    foreach (json_decode($quotes)->quotes as $quote){
        if ($quote->type == "INEFFECTIVE_LISTING_FEE"){
            wyswietl($offer, $quotes);
            break;
        }
    }
}*/

?>