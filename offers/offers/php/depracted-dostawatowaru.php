<?php
$file = fopen("../dostawatowaru.txt", "r");
$line = '';
while(! feof($file)) {
    $line = fgets($file);
}
print_r($line);
?>