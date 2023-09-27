<?php
include_once("../../allegrofunction.php");

$allegro = new AllegroServices();

$ean = trim($_GET['ean']);

$res = $allegro->sale("GET", "/offers/14404158983");

print_r(json_encode($res));
