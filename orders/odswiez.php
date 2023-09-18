<?php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

$fod = (isset($_POST['fod'])) ? $_POST['fod'] : $_GET['fod'];

print_r($fod);

function bladdodziennika($wpis)
{
    $plik = fopen("../bledy.txt", "a+");
    fwrite($plik, date('c') . ' ' . $wpis . "\r\n \r\n");
    fclose($plik);
}

include_once("../allegrofunction.php");
include_once("../../database.class.php");
$pdo = new DBconn();

$fodchf = '';
try {
    $fodchf = getRequestPublic('https://api.allegro.pl/order/checkout-forms/' . $fod);
    $fodchf = json_decode($fodchf);
} catch (PDOException $e) {
    echo ('Odswiez pobieranie fod-a ' . $e->getMessage());
}

$delivery = $pdo->prepare('SELECT fod FROM newallegrodelivery WHERE fod=:fod');
$delivery->bindValue(":fod", $fod, PDO::PARAM_STR);
$delivery->execute();
if ($delivery->rowCount() != 0) {
    $odswiez_delivery = $pdo->prepare('UPDATE newallegrodelivery SET addressname=:addressname,street=:street,city=:city,postcode=:postcode,companyname=:companyname,phonenumber=:phonenumber,methodid=:methodid,methodname=:methodname,pickuppoint=:pickuppoint,cost=:cost,smart=:smart,numberofpackages=:numberofpackages WHERE fod=:fod');
} else {
    $odswiez_delivery = $pdo->prepare('INSERT INTO newallegrodelivery (fod,addressname,street,city,postcode,companyname,phonenumber,methodid,methodname,pickuppoint,cost,smart,numberofpackages) VALUES (:fod,:addressname,:street,:city,:postcode,:companyname,:phonenumber,:methodid,:methodname,:pickuppoint,:cost,:smart,:numberofpackages)');
}
$delivery = $fodchf->delivery;
$pickuppoint = (!is_null($delivery->pickupPoint)) ? $delivery->pickupPoint->name . ' ' . $delivery->pickupPoint->address->zipCode . ' ' . $delivery->pickupPoint->address->city . ' ' . $delivery->pickupPoint->address->street : NULL;
$odswiez_delivery->bindValue(':fod', $fod, PDO::PARAM_STR);
$odswiez_delivery->bindValue(':addressname', $delivery->address->firstName . ' ' . $delivery->address->lastName, PDO::PARAM_STR);
$odswiez_delivery->bindValue(':street', $delivery->address->street, PDO::PARAM_STR);
$odswiez_delivery->bindValue(':city', $delivery->address->city, PDO::PARAM_STR);
$odswiez_delivery->bindValue(':postcode', $delivery->address->zipCode, PDO::PARAM_STR);
$odswiez_delivery->bindValue(':companyname', $delivery->address->companyName, PDO::PARAM_STR);
$odswiez_delivery->bindValue(':phonenumber', $delivery->address->phoneNumber, PDO::PARAM_STR);
$odswiez_delivery->bindValue(':methodid', $delivery->method->id, PDO::PARAM_STR);
$odswiez_delivery->bindValue(':methodname', $delivery->method->name, PDO::PARAM_STR);
$odswiez_delivery->bindValue(':pickuppoint', $pickuppoint, PDO::PARAM_STR);
$odswiez_delivery->bindValue(':cost', $delivery->cost->amount, PDO::PARAM_STR);
$odswiez_delivery->bindValue(':smart', $delivery->smart, PDO::PARAM_STR);
$odswiez_delivery->bindValue(':numberofpackages', $delivery->calculatedNumberOfPackages, PDO::PARAM_STR);
try {
    $odswiez_delivery->execute();
} catch (PDOException $e) {
    bladdodziennika('odswiez delivery ' . $e->getMessage());
}


$tests = $pdo->prepare('SELECT fod,id FROM newallegrosurcharges WHERE fod=:fod AND id=:id');
$test = $pdo->prepare('SELECT fod,id FROM newallegrosurcharges WHERE fod=:fod');
$surchargesadd = $pdo->prepare('INSERT INTO newallegrosurcharges (id,transactionid,fod,methodtype,methodprovider,finishedat,price) VALUES (:id,:transactionid,:fod,:methodtype,:methodprovider,:finishedat,:price)');

function transactionid($fod)
{
    global $pdo;
    $trid = $pdo->query('SELECT transactionid FROM newallegroorders WHERE fod="' . $fod . '"');
    $trid = $trid->fetch()['transactionid'];
    return $trid . '1';
}

$test->bindValue(":fod", $fod, PDO::PARAM_STR);
$test->execute();
try {
    $fodcf = getRequestPublic('https://api.allegro.pl/order/checkout-forms/' . $fod);
    $fodcf = json_decode($fodcf);
    if (count($fodcf->surcharges) != $test->rowCount()) {
        foreach ($fodcf->surcharges as $surcharges) {
            $tests->bindValue(":fod", $fod, PDO::PARAM_STR);
            $tests->bindValue(":id", $surcharges->id, PDO::PARAM_STR);
            $tests->execute();
            if ($tests->rowCount() == 0) {
                $transactionid = transactionid($fod);
                $surchargesadd->bindValue(":id", $surcharges->id, PDO::PARAM_STR);
                $surchargesadd->bindValue(":transactionid", $transactionid, PDO::PARAM_INT);
                $surchargesadd->bindValue(":fod", $fod, PDO::PARAM_STR);
                $surchargesadd->bindValue(":methodtype", $surcharges->type, PDO::PARAM_STR);
                $surchargesadd->bindValue(":methodprovider", $surcharges->provider, PDO::PARAM_STR);
                $surchargesadd->bindValue(":finishedat", $surcharges->finishedAt, PDO::PARAM_STR);
                $surchargesadd->bindValue(":price", $surcharges->paidAmount->amount, PDO::PARAM_STR);
                $surchargesadd->execute();
            }
        }
    }
} catch (PDOException $e) {
    echo ('odswiez ' . $e->getMessage());
}

print_r('juz');
