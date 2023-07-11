<?php
$limit = trim($_POST['limit']);
$group = trim($_POST['group']);
$gte = $_POST['gte'];
$lte = $_POST['lte'];
$offset = $_POST['offset'];
$login = trim($_POST['login']);
$operator = trim($_POST['operator']);

include_once("../allegrofunction.php");

function cmpw($a, $b){
    return strnatcmp($a->occurredAt, $b->occurredAt);
}

$login = ($login!='')?"&participant.login=".$login:'';
$group = ($group!="ALL")?"&group=".$group:'';
$operator = ($operator!="ALL")?"&wallet.paymentOperator=".$operator:'';
$offset = ($offset!='' & is_numeric($offset))?($offset-1)*$limit:"0";
$gte = '&occurredAt.gte='.date('Y-m-d\T\00:\00:\00.\0\0\0\Z',strtotime($gte));
$lte = '&occurredAt.lte='.date('Y-m-d\T\23:\59:\59.\0\0\0\Z',strtotime($lte));

$payment = getRequestPublic('https://api.allegro.pl/payments/payment-operations?limit='.$limit.'&offset='.$offset.$group.$gte.$lte.$operator.$login);
$payment = json_decode($payment);

$payment = $payment->paymentOperations;
usort($payment, "cmpw");
krsort($payment);
print_r(json_encode($payment));
?>