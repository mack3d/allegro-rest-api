<?php
include_once("../../database.class.php");
$pdo = new DBconn();

$braki = array();

$szukaj = $pdo->prepare('SELECT fod FROM newallegroorders WHERE paymentid=:paymentid');

$group = "INCOME";
$gte = date('Y-m-d', strtotime('-14 day'));
$lte = date('Y-m-d');

include_once("../allegrofunction.php");

for ($offset = 0; $offset < 6; $offset++) {
    $gte = '&occurredAt.gte=' . date('Y-m-d\T\00:\00:\00.\0\0\0\Z', strtotime($gte));
    $lte = '&occurredAt.lte=' . date('Y-m-d\T\23:\59:\59.\0\0\0\Z', strtotime($lte));

    $payment = getRequestPublic('https://api.allegro.pl/payments/payment-operations?limit=1&offset=' . $offset . "&group=INCOME" . $gte . $lte);
    $payment = json_decode($payment);

    $payment = $payment->paymentOperations;

    echo count($payment) . '<br>';

    $paycount = count($payment);
    for ($i = 0; $i < $paycount; $i++) {
        $szukaj->bindValue(":paymentid", $payment[$i]->payment->id, PDO::PARAM_STR);
        $szukaj->execute();
        if ($szukaj->rowCount() < 2) {
            echo $payment[$i]->payment->id . '<br>';
        }
    }
}
