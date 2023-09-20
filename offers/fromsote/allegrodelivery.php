<?php
include_once("../../allegrofunction.php");

$allegro = new AllegroServices();
$delivery = $allegro->sale("GET", '/shipping-rates');

print_r(json_encode($delivery));
