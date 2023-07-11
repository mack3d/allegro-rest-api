<html>
<head>
<meta charset="UTF-8">
<title>Allegro zamówienie</title>
<meta name="author" content="Maciej Krupiński">
<link rel="stylesheet" href="stylec.css">
</head>
<body>
<?php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

include_once("../allegrofunction.php");

$pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4','root','');$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if(isset($_GET['fod']) & isset($_GET['co'])){
    $fod = getRequest('https://api.allegro.pl/order/checkout-forms/'.$_GET['fod']);
    $fod = json_decode($fod);
    $query = '';
    if($_GET['co']=="order" | $_GET['co']=="payment"){
        $ask = $pdo->prepare('SELECT * FROM newallegroorders WHERE fod=:fod');
        if(strpos($_SERVER['HTTP_REFERER'],"create.php")){
            $query = 'UPDATE newallegroorders SET';
            foreach($_GET as $k => $v){
                $kv = ($v!='')?$k.'="'.$v.'"':$k.'=NULL';
                $query .= ($k!="fod" & $k!="co" & $k!="jak")?' '.$kv.',':'';
            }
            $query = substr($query,0,-1);
            $query .= ' WHERE fod="'.$_GET['fod'].'"';
        }
    }
    if($_GET['co']=="buyer"){
        $ask = $pdo->prepare('SELECT * FROM newallegrobuyer WHERE fod=:fod');
    }
    if($_GET['co']=="invoice"){
        $ask = $pdo->prepare('SELECT * FROM newallegroinvoice WHERE fod=:fod');
        if(strpos($_SERVER['HTTP_REFERER'],"create.php")){
            if($_GET['jak']!="insert"){
                $query = 'UPDATE newallegroinvoice SET';
                foreach($_GET as $k => $v){
                    $kv = ($v!='')?$k.'="'.$v.'"':$k.'=NULL';
                    $query .= ($k!="fod" & $k!="co" & $k!="jak")?' '.$kv.',':'';
                }
                $query = substr($query,0,-1);
                $query .= ' WHERE fod="'.$_GET['fod'].'"';
            }else{
                $query = 'INSERT INTO newallegroinvoice (';
                foreach($_GET as $k => $v){
                    $query .= ($k!="co" & $k!="jak")?$k.',':'';
                }
                $query = substr($query,0,-1);
                $query .= ') VALUES (';
                foreach($_GET as $k => $v){
                    $query .= ($k!="co" & $k!="jak")?'"'.$v.'",':'';
                }
                $query = substr($query,0,-1);
                $query .= ')';
            }
        }
    }
    if($_GET['co']=="delivery"){
        $ask = $pdo->prepare('SELECT * FROM newallegrodelivery WHERE fod=:fod');
        if(strpos($_SERVER['HTTP_REFERER'],"create.php")){
            if($_GET['jak']!="insert"){
                $query = 'UPDATE newallegrodelivery SET';
                foreach($_GET as $k => $v){
                    $kv = ($v!='')?$k.'="'.$v.'"':$k.'=NULL';
                    $query .= ($k!="fod" & $k!="co" & $k!="jak")?' '.$kv.',':'';
                }
                $query = substr($query,0,-1);
                $query .= ' WHERE fod="'.$_GET['fod'].'"';
            }else{
                $query = 'INSERT INTO newallegrodelivery (';
                foreach($_GET as $k => $v){
                    $query .= ($k!="co" & $k!="jak")?$k.',':'';
                }
                $query = substr($query,0,-1);
                $query .= ') VALUES (';
                foreach($_GET as $k => $v){
                    $query .= ($k!="co" & $k!="jak")?'"'.$v.'",':'';
                }
                $query = substr($query,0,-1);
                $query .= ')';
            }
        }
    }

    if ($query!=''){
        try{
            $pdo->exec($query);
            header('Location: order.php?fod='.$_GET['fod']);
        }catch(PDOException $e){echo ('kreator '.$e->getMessage());}
    }

    $ask->bindValue(":fod",$_GET['fod'], PDO::PARAM_STR);
    $ask->execute();
    echo '<form method="get">';
    if($ask->rowCount()>0){
        $ask = $ask->fetch(PDO::FETCH_OBJ);
        foreach($ask as $k => $v){
            echo '<li><input class="key" value="'.strtoupper($k).'" disabled><input class="val" type="text" name="'.$k.'" value="'.$v.'"></li>';
        }
        echo '<li><input hidden name="co" value="'.$_GET['co'].'"><input hidden name="jak" value="update"><input class="zapisz" type="submit" value="ZAPISZ"></li></form>';
    }else{
        if($_GET['co']=="invoice"){
            $ask = $pdo->prepare('DESCRIBE newallegroinvoice');
        }
        if($_GET['co']=="delivery"){
            $ask = $pdo->prepare('DESCRIBE newallegrodelivery');
        }
        $ask->execute();
        $ask = $ask->fetchAll(PDO::FETCH_COLUMN);
        foreach($ask as $v){
            echo '<li><input class="key" value="'.strtoupper($v).'" disabled><input class="val" type="text" name="'.$v.'" value="';
            echo ($v=="fod")?$_GET['fod']:'';
            echo '"></li>';
        }
        echo '<li><input hidden name="co" value="'.$_GET['co'].'"><input hidden name="jak" value="insert"><input class="zapisz" type="submit" value="ZAPISZ"></li></form>';
    }




    echo '<pre>';print_r($fod);echo'</pre>';
}

?>
</body>
</html>