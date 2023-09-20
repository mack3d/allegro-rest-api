<?php
include_once("../../allegrofunction.php");
$allegro = new AllegroServices();
$id = $_POST['id'];

$name = array();
$x = 1;
while ($x > 0) {
    $cat = $allegro->sale("GET", "/categories/{$id}");
    if (isset($cat->parent->id)) {
        $id = $cat->parent->id;
        array_push($name, array("id" => $id, "name" => $cat->name));
    } else {
        break;
    }
}

print_r(json_encode($name));
