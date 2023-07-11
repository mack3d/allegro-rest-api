<pre><?php
include_once("../../../allegrofunction.php");

function getOffers(){
    $offers = allegro('GET', '/sale/offers/12683141460/smart');
    return json_decode($offers);
}


print_r(getOffers());
?>