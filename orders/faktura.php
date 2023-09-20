<?php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

function bladdodziennika($wpis)
{
	$plik = fopen("../bledy.txt", "a+");
	fwrite($plik, date('c') . ' ' . $wpis . "\r\n \r\n");
	fclose($plik);
}

$fod = (isset($_POST['fod'])) ? $_POST['fod'] : $_GET['fod'];
include_once("../allegrofunction.php");

include_once("../../database.class.php");
$pdo = new DBconn();

$allegro = new AllegroServices();

$allegrofod = $allegro->order("GET", "/checkout-forms/{$fod}");

$invoiceadd = $pdo->prepare('INSERT INTO newallegroinvoice (fod,street,city,zipcode,companyname,companytaxid,naturalperson) VALUES (:fod,:street,:city,:zipcode,:companyname,:companytaxid,:naturalperson)');
$invoice = $allegrofod->invoice;
$companyname = NULL;
$companytaxid = NULL;
$naturalperson = NULL;
if (!is_null($invoice->address->company)) {
	$companyname = $invoice->address->company->name;
	$companytaxid = $invoice->address->company->taxId;
}
if (!is_null($invoice->address->naturalPerson)) {
	$naturalperson = $invoice->address->naturalPerson->firstName . ' ' . $invoice->address->naturalPerson->lastName;
}
$invoiceadd->bindValue(':fod', $fod, PDO::PARAM_STR);
$invoiceadd->bindValue(':street', $invoice->address->street, PDO::PARAM_STR);
$invoiceadd->bindValue(':city', $invoice->address->city, PDO::PARAM_STR);
$invoiceadd->bindValue(':zipcode', $invoice->address->zipCode, PDO::PARAM_STR);
$invoiceadd->bindValue(':companyname', $companyname, PDO::PARAM_STR);
$invoiceadd->bindValue(':companytaxid', $companytaxid, PDO::PARAM_STR);
$invoiceadd->bindValue(':naturalperson', $naturalperson, PDO::PARAM_STR);
try {
	$invoiceadd->execute();
} catch (PDOException $e) {
	bladdodziennika('invoiceadd' . $e->getMessage());
}

header("Location: ./order.php?fod=" . $_GET['fod']);
