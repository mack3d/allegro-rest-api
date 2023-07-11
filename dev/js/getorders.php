<?php
$pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4','root','');$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$orderslimit = $_POST['limit'];
$ordersoffset = $_POST['offset'];

$totalCount = $pdo->query('SELECT COUNT(fod) as order_count FROM newallegroorders');
$totalCount = $totalCount->fetch()['order_count'];

$newOrdersCount = $pdo->query('SELECT COUNT(fod) as order_count FROM newallegroorders WHERE statusfod = "READY_FOR_PROCESSING"');
$newOrdersCount = $newOrdersCount->fetch()['order_count'];

$ready = $pdo->prepare('SELECT newallegroorders.itemid,newallegroorders.transactionid,newallegroorders.summary,newallegroorders.fod,newallegroorders.readytime,newallegroorders.buyerlogin,newallegroorders.deliverymethod,newallegrobuyer.username,newallegroorders.statusfod FROM newallegroorders LEFT JOIN newallegrobuyer USING(fod) WHERE newallegroorders.readytime IS NOT NULL ORDER BY CASE newallegroorders.statusfod WHEN "READY_FOR_PROCESSING" THEN 0 ELSE 1 END, newallegroorders.readytime DESC LIMIT :orderslimit OFFSET :ordersoffset');
$ready->bindValue(':orderslimit', $orderslimit, PDO::PARAM_INT);
$ready->bindValue(':ordersoffset', $ordersoffset, PDO::PARAM_INT);
$ready->execute();
$results = $ready->fetchAll(PDO::FETCH_ASSOC);

$response = array("orders" => $results, "count" => count($results), "totalCount" => $totalCount, "newOrdersCount" => $newOrdersCount);

print_r(json_encode($response));
?>