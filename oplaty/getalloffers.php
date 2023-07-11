<?php
include_once("../allegrofunction.php");

$ao = getalloffers();

print_r(json_encode($ao));
?>