<?php
$categoryid = $_GET['categoryid'];

$i = getRequestPublic('https://api.allegro.pl/sale/categories/'.$categoryid.'/parameters');
print_r($i);
?>