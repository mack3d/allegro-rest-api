<pre><?php
// GENEROWANIE I ZAPIS KODOW DO BAZY
$pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4','root','');$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$upd = $pdo->prepare('UPDATE newallegrolineitems SET offerexternal=:offerexternal WHERE id=:id');

$eid = $pdo->prepare('SELECT id, offername, offerexternal FROM newallegrolineitems WHERE offerexternal IS NULL');
$eid->execute();

echo '<table>';
foreach($eid->fetchAll() as $item){
    if(is_numeric(substr($item['offername'],-4))){
        $upd->bindValue(":offerexternal",substr($item['offername'],-4), PDO::PARAM_STR);
        $upd->bindValue(":id",$item['id'], PDO::PARAM_STR);
        $upd->execute();
    }else{
        $kod='';
        $nazwa = $item['offername'];
        for($x=1;$x<7;$x++){
            (is_numeric(substr($nazwa,-$x,1)))?$kod=substr($nazwa,-$x,1).$kod:'';if(strlen($kod)==4)break;
        }
        if ($kod!=''){
            $name = substr($nazwa,0,strpos($nazwa,$kod));
            $name = (substr($name,-1,1)=="[")?trim(substr($name,0,-1)):trim($name);
            $kkod = substr($nazwa,strpos($nazwa,$kod));
            $kkod = preg_replace('/[^a-zA-Z0-9-+ ]/', '', $kkod);
        }else{
            $kkod = '';
            $name = $nazwa;
        }
        echo '<tr><td>'.$item['offername'].'</td><td>'.$name.'</td><td>'.$kkod.'</td></tr>';
    }
}   
echo '</table>';
?>