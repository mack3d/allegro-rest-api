<pre>
<?php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

$pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function con($str){
	if(strlen($str)>0){
		return mb_convert_encoding($str, 'Windows-1252', 'UTF-8');
	}else{
		return $str;
	}
}

function da($r){
	global $pdo,$tabela;
	$kol='';$wstaw='';
	foreach($r as $k=>&$v){
		$kol.=$k.',';
		$wstaw.=':'.$k.',';
	}
	$add = $pdo->prepare('INSERT INTO mb'.$tabela.' ('.substr($kol,0,-1).') VALUES ('.substr($wstaw,0,-1).')');

	foreach($r as $k=>&$v){
		$add->bindValue($k, con($v));
	}
	$add->execute();
}

$tabelki = array('newallegroevents','newallegroorders','newallegromessage','newallegrobuyer','newallegrodelivery','newallegroinvoice','newallegrolineitems','newallegrosurcharges');

foreach($tabelki as $tabela){
	$porownanie = ($tabela=="newallegroevents" | $tabela=="newallegrolineitems")?"id":"fod";
	$e = $pdo->prepare('SELECT '.$tabela.'.* FROM '.$tabela.' LEFT JOIN mb'.$tabela.' USING('.$porownanie.') WHERE mb'.$tabela.'.'.$porownanie.' IS NULL LIMIT 1500');
	$e->execute();
	$e->setFetchMode(PDO::FETCH_ASSOC);
	foreach($e as $r){
		print_r($r);
		da($r);
	}
}
?>