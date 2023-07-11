<?php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

include_once("../allegrofunction.php");

function bladdodziennika($wpis){
	$plik = fopen("../bledy.txt", "a+");
	fwrite($plik,date('c').' statusfod '.$wpis."\r\n \r\n");
	fclose($plik);
}
function save($fod,$status){
    global $pdo,$upd;
    $upd->bindValue(":statusfod",$status, PDO::PARAM_STR);
    $upd->bindValue(":fod",$fod, PDO::PARAM_STR);
    try{
        $upd->execute();
    }catch(PDOException $e){bladdodziennika($fod[0]."\r\n".$e->getMessage());}
}

$pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4','root','');$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$upd = $pdo->prepare('UPDATE newallegroorders SET statusfod=:statusfod WHERE fod=:fod');
$test = $pdo->prepare('SELECT statusfod FROM newallegroorders WHERE fod=:fod');

if(isset($_POST['status'])){
    $fod = explode('//',$_POST['status'])[0];
    $status = explode('//',$_POST['status'])[1];
    $statusindex = explode('//',$_POST['status'])[2];
    $test->bindValue(":fod",$fod, PDO::PARAM_STR);
    $test->execute();
    $test = $test->fetch()['statusfod'];
    $wrealizacji = array("status"=>'PROCESSING');
    if($statusindex == 5 or $statusindex == 4){
        if($test=="READY_FOR_PROCESSING"){
            $status = "COMPLETING";
            $statusindex = 4;
            putPublic('https://api.allegro.pl/order/checkout-forms/'.$fod.'/fulfillment',$wrealizacji);
        }
    }
    if($status!="PRINT"){
        save($fod,$status);
    }

    echo $statusindex;
}
?>