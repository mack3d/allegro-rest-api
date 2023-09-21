<pre>
<?php
include_once("../allegrofunction.php");

$allegro = new AllegroServices();

function cmpw($a, $b)
{
    return strnatcmp($a->occurredAt, $b->occurredAt);
}

$payment = $allegro->billing("GET", '/billing-types');

print_r($payment);
?>