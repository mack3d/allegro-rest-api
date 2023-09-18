<pre>
<?php
include_once("../allegrofunction.php");
require_once('../../tcpdf/tcpdf.php');

$paymentId = $_GET['numer'];

function getBillingHistory($offset = 0, $group = null, $occurredAtGte = null, $occurredAtLte = null)
{
    $offset *= 100;
    $group = (!is_null($group)) ? "&group={$group}" : '';
    $occurredAtGte = (!is_null($occurredAtGte)) ? "&occurredAt.gte={$occurredAtGte}" : '';
    $occurredAtLte = (!is_null($occurredAtLte)) ? "&occurredAt.lte={$occurredAtLte}" : '';
    $pay = getRequestPublic('https://api.allegro.pl/payments/payment-operations?limit=100&offset=' . $offset . $group . $occurredAtGte . $occurredAtLte);
    $pay = json_decode($pay);
    return $pay->paymentOperations;
}

function getbillingData($id)
{
    $resp = null;
    $nextLoop = 0;
    while ($nextLoop >= 0) {
        $history = getBillingHistory($nextLoop, 'OUTCOME');
        foreach ($history as $pay) {
            if ($pay->payout->id === $id) {
                $resp = $pay;
                $nextLoop = -1;
                break;
            }
        }
    }
    return $resp;
}

$paymentData = getbillingData($paymentId);
print_r($paymentData);
