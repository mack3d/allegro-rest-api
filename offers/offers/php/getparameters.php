<?php
$categoryid = $_GET['categoryid'];
$allegro = new AllegroServices();
$i = $allegro->sale("GET", "/categories/{$categoryid}/parameters");
print_r(json_decode($i));
