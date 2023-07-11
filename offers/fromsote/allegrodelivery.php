<pre><?php
include_once("../../allegrofunction.php");

$delivery = getRequestPublic('https://api.allegro.pl/sale/shipping-rates');
$delivery = json_decode($delivery);


print_r($delivery);
?>