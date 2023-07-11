<?php
$data = json_decode(file_get_contents('php://input'), true);

$method = strtoupper(trim($data['method']));
$endpoint = trim($data['endpoint']);
$search = (isset($data['search']))?$data['search']:array();
$params = (isset($data['search']))?$data['search']:array();

$url = "https://api.allegro.pl{$endpoint}";
if (count($search) > 0){
    $url .= "?";
    foreach($search as $key => $val){
        $url .= "{$key}={$val}&";
    }
}

function connect($method, $url, array $params = []) {
	$headers = ['Accept: application/vnd.allegro.public.v1+json','Authorization: Bearer '.$_COOKIE['tokenn']];
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$data = json_encode($params);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);   
	return curl_exec($curl);
}

$c = connect($method, $url, $params);
print_r($c);
?>