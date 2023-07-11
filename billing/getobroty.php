<?php
$gte = $_POST['gte'];
$lte = $_POST['lte'];
$wplaty = array();
$obrot=0;

include_once("../allegrofunction.php");
$gte = "2022-11-01";
$lte = "2022-11-30";
$gte = '&occurredAt.gte='.date('Y-m-d\T\00:\00:\00.\0\0\0\Z',strtotime($gte));
$lte = '&occurredAt.lte='.date('Y-m-d\T\23:\59:\59.\0\0\0\Z',strtotime($lte));

function pobierz($limit,$offset,$lte,$gte){
    $payment = getRequestPublic('https://api.allegro.pl/payments/payment-operations?group=INCOME&limit='.$limit.'&offset='.$offset.$gte.$lte);
    return json_decode($payment);
}

$payment = pobierz(1,0,$lte,$gte);
//echo $payment->totalCount;
//print_r($payment);
$ilepteli = ceil($payment->totalCount/100);

for($i=0;$i<$ilepteli;$i++){
    $limit = 100;
    if ($i+1==$ilepteli & $payment->totalCount!=100){
        $limit = $payment->totalCount%100;
    }
    $porcja = pobierz($limit,$i,$lte,$gte);
    $wplaty = array_merge($wplaty,$porcja->paymentOperations); 
}

foreach($wplaty as $pay){
    $obrot+=$pay->value->amount;
}
print_r($obrot);
?>