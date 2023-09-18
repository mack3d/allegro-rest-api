<?php
include_once("../../database.class.php");
$pdo = new DBconn();

$fod = $_GET['fod'];

$pdo->query('DELETE FROM newallegroorders WHERE fod="'.$fod.'"');
$pdo->query('DELETE FROM newallegrobuyer WHERE fod="'.$fod.'"');
$pdo->query('DELETE FROM newallegrodelivery WHERE fod="'.$fod.'"');
$pdo->query('DELETE FROM newallegrolineitems WHERE fod="'.$fod.'"');
$pdo->query('DELETE FROM newallegroinvoice WHERE fod="'.$fod.'"');
$pdo->query('DELETE FROM newallegromessage WHERE fod="'.$fod.'"');
$pdo->query('DELETE FROM newallegrosurcharges WHERE fod="'.$fod.'"');

header('Location: ../index.php');
