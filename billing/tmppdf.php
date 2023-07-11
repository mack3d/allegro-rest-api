<pre><?php
$url = '';
/*include_once("../allegrofunction.php");

$pdo = new PDO('mysql:host=localhost;dbname=satserwis','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$fod = $pdo->prepare('SELECT transactionid,readytime FROM newallegroorders WHERE paymentid=:paymentid');*/

foreach($_GET as $numer){
    $url.="&cb[]=".$numer;
    /*$payment = getRequestPublic('https://api.allegro.pl/payments/payment-operations?payment.id='.$numer);
    $payment = json_decode($payment);

    $fod->bindValue(':paymentid', $numer, PDO::PARAM_STR);
    $fod->execute();
    $e = $fod->fetch(PDO::FETCH_ASSOC);
    print_r($e);*/
}

header("Location: pdfzwroty.php?".$url);
?>