<pre><?php
require_once __DIR__."/vendor/autoload.php";
$mongo = new MongoDB\Client("mongodb://localhost:27017");
$allegro = $mongo->allegro;
$orders = $allegro->orders;

function getorder($query){
    global $orders;
    $item = $orders->findOne($query);
    return $item;
}

function updateorder($query,$sets){
    global $orders;
    $item = $orders->updateOne($query,$sets);
    return $item;
}

$i = getorder(array('id' => '93e74301-8baa-11eb-9ee0-9b7301159bde'));

print_r($i);


$i = updateorder(array('id' => '93e74301-8baa-11eb-9ee0-9b7301159bde'),array('number' => '123456789'));

print_r($i->getModifiedCount());

?>