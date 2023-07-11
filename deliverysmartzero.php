<html>
<body>
<?php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

if(!isset($poczatek)){$poczatek=date("Y-m")."-01";}
if(!isset($koniec)){$koniec=date("Y-m")."-31";}
echo '
<form method="POST">
<input type="text" name="loginy" placeholder="Loginy">
<input type="date" name="poczatek" value="'.$poczatek.'">
<input type="date" name="koniec" value="'.$koniec.'">
<input type="submit" value="Szukaj">
</form>';
?>
<pre>
<?php
@$loginy = $_POST['loginy'];
@$poczatek = $_POST['poczatek'];
@$koniec = $_POST['koniec'];
$pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4','root','');$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if(trim($loginy)!=''){
$zero = $pdo->prepare('SELECT newallegroorders.fod as fod,newallegrodelivery.smart as smart, newallegroorders.buyerlogin as blogin FROM newallegroorders LEFT JOIN newallegrodelivery USING(fod) WHERE FIND_IN_SET(newallegroorders.buyerlogin,:loginy) AND newallegroorders.shipmenttime>:poczatek AND newallegroorders.shipmenttime<:koniec');
$zero->bindValue(":loginy", $loginy, PDO::PARAM_STR);
}else{
$zero = $pdo->prepare('SELECT newallegroorders.fod as fod,newallegrodelivery.smart as smart, newallegroorders.buyerlogin as blogin FROM newallegroorders LEFT JOIN newallegrodelivery USING(fod) WHERE newallegrodelivery.smart="1" AND newallegroorders.shipmenttime>:poczatek AND newallegroorders.shipmenttime<:koniec');
}

$zero->bindValue(":poczatek", $poczatek, PDO::PARAM_STR);
$zero->bindValue(":koniec", $koniec, PDO::PARAM_STR);
$zero->execute();
echo $zero->rowCount().'<br>';
$zero = $zero->fetchAll();

$l=0;
echo '<table>';
foreach($zero as $z){
    echo '<tr><td>'.++$l.'</td><td>'.$z['fod'].'</td><td>'.$z['blogin'].'</td><td>'.$z['smart'].'</td></tr>';
}
echo '</table>';
?>

</body></html>