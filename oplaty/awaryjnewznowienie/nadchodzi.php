<pre><?php
include_once("../allegrofunction.php");


$daneoferty = getRequestPublic('https://api.allegro.pl/pricing/offer-quotes?offer.id=9099900912');

print_r(json_decode($daneoferty));


?>