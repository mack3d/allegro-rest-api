<html>
    <head></head>
<body>

<pre>
<?php
    include_once("allegrofunction.php");

    $orders = getRequestPublic('https://api.allegro.pl/order/checkout-forms');
    $orders = json_decode($orders);

    print_r($orders);

    

    require_once __DIR__."/vendor/autoload.php";
    $mongo = new MongoDB\Client("mongodb://localhost:27017");
    $allegro = $mongo->allegro;
    $orders = $allegro->orders;

?>
</pre>

</body>
</html>

