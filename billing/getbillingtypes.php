<pre>
<?php
include_once("../allegrofunction.php");

function cmpw($a, $b){
    return strnatcmp($a->occurredAt, $b->occurredAt);
}

$payment = getRequestPublic('https://api.allegro.pl/billing/billing-types');
$payment = json_decode($payment);

print_r($payment);
?>