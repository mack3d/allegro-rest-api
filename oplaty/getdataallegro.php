<?php
include_once("../allegrofunction.php");

$offerid = numerek($_POST['offerid']);

function numerek($tytul){
    $tytul = explode('(',$tytul);
    $tytul = explode(')',$tytul[1]);
    return $tytul[0];
}

$resp = array("id"=>$_POST['offerid']);
print_r(json_encode($resp));
?>