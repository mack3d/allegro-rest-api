<?php
include_once("../../allegrofunction.php");

$categoryId = $_POST['id'];

$parametry = getRequestPublic('https://api.allegro.pl/sale/categories/'.$categoryId.'/parameters');

print_r($parametry);
?>