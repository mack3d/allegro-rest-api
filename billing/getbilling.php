<pre><?php
$limit = 100;
$offset = 0;
$offset *= $limit;
$orderid = '78de0337-0c0c-11ed-be81-57847384b812';
include_once("../allegrofunction.php");

function cmpw($a, $b){
    return strnatcmp($a->occurredAt, $b->occurredAt);
}


#$payment = getRequestPublic('https://api.allegro.pl/billing/billing-entries?limit='.$limit.'&offset='.$offset);
$payment = getRequestPublic('https://api.allegro.pl/billing/billing-entries?order.id='.$orderid);
$payment = json_decode($payment);

print_r($payment);

$tagi = array('SUC','REF','HB4','PKO','HB1','HB2');
foreach ($payment->billingEntries as $pay){
    if (!in_array($pay->type->id,$tagi)){
        //if ($pay->order->id == $orderid){
            print_r($pay);
        //}
    }
}

/*rint_r($payment);
foreach ($payment->billingEntries as $pay){
    if (isset($pay->order)){
        if ($pay->order->id == $orderid){
            print_r($pay);
        }
    }
}*/
?>