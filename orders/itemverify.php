<?php
$data = json_decode(file_get_contents('php://input'), true);

$externalid = $data['externalid'];

preg_match_all("/\d{4,5}/", $externalid, $codes);

$codes = implode(',', $codes[0]);

include_once("../../database.class.php");
$pdo = new DBconn();
$dane = $pdo->prepare('SELECT nazwa, kodn, cena, ilosc FROM fpp WHERE FIND_IN_SET(kodn, :kod)');

$dane->bindValue(":kod", $codes, PDO::PARAM_STR);
$dane->execute();

$resp = array();
if ($dane->rowCount()>0){
    foreach($dane->fetchAll(PDO::FETCH_OBJ) as $d){
        array_push($resp, $d);
    }
}

print_r(json_encode($resp));
