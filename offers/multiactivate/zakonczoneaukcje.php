<pre><?php
include_once("../../allegrofunction.php");

$offers = array();
$i = 0;
while (true){
    $aukcje = getRequestPublic('https://api.allegro.pl/sale/offers?publication.status=ENDED&sort=-stock.sold&limit=1000&offset='.$i*1000);
    $aukcje = json_decode($aukcje);
    if (($i*1000) > $aukcje->totalCount){
        break;
    }
    $offers = array_merge($offers,$aukcje->offers);
    $i++;
}

function fppdata($code){
    $pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4','root','');$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dane = $pdo->prepare('SELECT * FROM fpp WHERE kodn=:kod');
    $dane->bindValue(":kod", $code, PDO::PARAM_STR);
    $dane->execute();
    if ($dane->rowCount()>0){
        return $dane->fetch();
    }else{
        return 0;
    }
}

//print_r($offers)

foreach ($offers as $offer){
    if (isset($offer->external)){
        $fpp = fppdata($offer->external->id);
        if ($fpp != 0){
            if ($fpp['ilosc'] > 0){
                echo '<br>'.$offer->id.' '.$offer->name;
                echo ' '.$offer->external->id;
                echo ($fpp != 0)?' '.$fpp['ilosc']:'';
                
            }
        }
    }
}
?>