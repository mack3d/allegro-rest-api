<?php
include_once("../../allegrofunction.php");
$allegro = new AllegroServices();

$categoryId = $_POST['id'];

$parametry = $allegro->sale("GET", "/categories/{$categoryId}/parameters");

print_r(json_encode($parametry));
