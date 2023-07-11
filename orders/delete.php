<?php
$pdo = new PDO('mysql:host=localhost;dbname=satserwis','root','');$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$fod = $_GET['fod'];

$pdo->query('DELETE FROM newallegroorders WHERE fod="'.$fod.'"');
$pdo->query('DELETE FROM newallegrobuyer WHERE fod="'.$fod.'"');
$pdo->query('DELETE FROM newallegrodelivery WHERE fod="'.$fod.'"');
$pdo->query('DELETE FROM newallegrolineitems WHERE fod="'.$fod.'"');
$pdo->query('DELETE FROM newallegroinvoice WHERE fod="'.$fod.'"');
$pdo->query('DELETE FROM newallegromessage WHERE fod="'.$fod.'"');
$pdo->query('DELETE FROM newallegrosurcharges WHERE fod="'.$fod.'"');

header('Location: ../index.php');
?>