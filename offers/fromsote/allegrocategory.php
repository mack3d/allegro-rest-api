<?php
include_once("../../allegrofunction.php");

$id = $_POST['id'];

$name = array();
$x = 1;
while ($x > 0){
    $cat = getRequestPublic('https://api.allegro.pl/sale/categories/'.$id);
    $cat = json_decode($cat);
    if (isset($cat->parent->id)){
        $id = $cat->parent->id;
        array_push($name, array("id"=>$id, "name"=>$cat->name));
    }else{
        break;
    }
}

print_r(json_encode($name));
?>