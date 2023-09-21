<?php

$data = json_decode(trim(file_get_contents("php://input")));

$limit = trim($data->limit);
$group = trim($data->group);
$gte = $data->gte;
$lte = $data->lte;
$offset = $data->offset;
$login = trim($data->login);
$operator = trim($data->operator);

include_once("../allegrofunction.php");

$allegro = new AllegroServices();

function cmpw($a, $b)
{
    return strnatcmp($a->occurredAt, $b->occurredAt);
}

$login = ($login != '') ? "&participant.login=" . $login : '';
$group = ($group != "ALL") ? "&group=" . $group : '';
$operator = ($operator != "ALL") ? "&wallet.paymentOperator=" . $operator : '';
$offset = ($offset != '' & is_numeric($offset)) ? ($offset - 1) * $limit : "0";
$gte = '&occurredAt.gte=' . date('Y-m-d\T\00:\00:\00.\0\0\0\Z', strtotime($gte));
$lte = '&occurredAt.lte=' . date('Y-m-d\T\23:\59:\59.\0\0\0\Z', strtotime($lte));

$payment = $allegro->payments("GET", "/payment-operations?limit={$limit}&offset={$offset}{$group}{$gte}{$lte}{$operator}{$login}");

$payment = $payment->paymentOperations;
usort($payment, "cmpw");
krsort($payment);
print_r(json_encode($payment));
