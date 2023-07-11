<?php
include_once("../../allegrofunction.php");

$offerid = $_POST['offerid'];

$daneoferty = getRequestPublic('https://api.allegro.pl/pricing/offer-quotes?offer.id='.$offerid);

print_r($daneoferty);
?>