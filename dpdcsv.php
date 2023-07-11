<?php
header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=".date('Y-m-d_His').'.csv');

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

$od = $_POST['od'];

$pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4','root','');$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$adresy = $pdo->prepare('SELECT newallegrodelivery.companyname,newallegrodelivery.addressname,newallegrodelivery.street,newallegrodelivery.city,newallegrodelivery.postcode,newallegrodelivery.phonenumber,newallegrobuyer.email FROM newallegrodelivery LEFT JOIN newallegroorders USING(fod) LEFT JOIN newallegrobuyer USING(fod) WHERE methodname LIKE "%DPD%" and newallegroorders.paymentfinished>=:od');
$adresy->bindValue(':od', $od, PDO::PARAM_STR);
$adresy->execute();


$csv = fopen('php://output', 'w');

//fputcsv($csv, array("companyname", "addressname", "street", "city", "postcode" ,"phonenumber", "email"), ";");

foreach($adresy->fetchAll(PDO::FETCH_ASSOC) as $i => $d){
    fputcsv($csv, $d, ";");
}

fclose($csv);

?>