<?php
$data = json_decode(file_get_contents('php://input'), true);

$externalid = $data['externalid'];

preg_match_all("/\d{4,5}/", $externalid, $codes);

$codes = implode(',', $codes[0]);

$pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4','root','');$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
?>