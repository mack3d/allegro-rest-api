<?php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

include_once("../allegrofunction.php");

include_once("../../database.class.php");

$pdo = new DBconn();
$allegro = new AllegroServices();

function bladdodziennika($wpis)
{
    $plik = fopen("../bledy.txt", "a+");
    fwrite($plik, date('c') . ' statusfod ' . $wpis . "\r\n \r\n");
    fclose($plik);
}
function save($pdo, $fod, $status)
{
    $upd = $pdo->prepare('UPDATE newallegroorders SET statusfod=:statusfod WHERE fod=:fod');
    $upd->bindValue(":statusfod", $status, PDO::PARAM_STR);
    $upd->bindValue(":fod", $fod, PDO::PARAM_STR);
    try {
        $upd->execute();
    } catch (PDOException $e) {
        bladdodziennika($fod[0] . "\r\n" . $e->getMessage());
    }
}

if (isset($_GET['fod'])) {
    $fod = $_GET['fod'];
    $status = $_GET['statusfodslect'];
    $statusindex = $_GET['indexselect'];
    $test = $pdo->prepare('SELECT statusfod FROM newallegroorders WHERE fod=:fod');
    $test->bindValue(":fod", $fod, PDO::PARAM_STR);
    $test->execute();
    $test = $test->fetch()['statusfod'];
    $wrealizacji = array("status" => 'PROCESSING');
    if ($statusindex == 5 or $statusindex == 4) {
        if ($test == "READY_FOR_PROCESSING") {
            $status = "COMPLETING";
            $statusindex = 4;
            $allegro->order("PUT", "/checkout-forms/{$fod}/fulfillment", $wrealizacji);
        }
    }
    if ($status != "PRINT") {
        save($pdo, $fod, $status);
    }
    echo json_encode((array("statusindex" => $statusindex)));
}
