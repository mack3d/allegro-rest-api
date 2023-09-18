<?php
$data = json_decode(file_get_contents('php://input'), true);

$codes = $data['codes'];
$allegroCodes = $data['allegroCodes'];

$codes = (empty($codes)) ? $allegroCodes : $codes;

include_once("../../../../database.class.php");
$pdo = new DBconn();

$dane = $pdo->prepare('SELECT nazwa,kodn,cena,ilosc FROM fpp WHERE FIND_IN_SET(kodn, :kod) ORDER BY cena DESC');

$dane->bindValue(":kod", implode(',', $codes), PDO::PARAM_STR);
$dane->execute();

$resp = array();
if ($dane->rowCount() > 0) {
    foreach ($dane->fetchAll() as $d) {
        $resp[$d['kodn']] = json_decode(json_encode($d));
    }
    $resp['count'] = count($resp);
}

print_r(json_encode($resp));
