<?php
function getRequest($uri, array $params = []) {
	$headers = ['Accept: application/vnd.allegro.beta.v1+json','Content-Type: application/vnd.allegro.public.v1+json','Authorization: Bearer '.$_COOKIE['tokenn'],'Api-Key: c77d0744d4'];
	$curl = curl_init($uri);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$data = json_encode($params);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);   
	return curl_exec($curl);
}
function getRequestPublic($uri, array $params = []) {
	$headers = ['Accept: application/vnd.allegro.public.v1+json','Content-Type: application/vnd.allegro.public.v1+json','Authorization: Bearer '.$_COOKIE['tokenn'],'Api-Key: c77d0744d4'];
	$curl = curl_init($uri);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$data = json_encode($params);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);   
	return curl_exec($curl);
}
function postPublic($uri, array $params = []) {
	$headers = ['Accept: application/vnd.allegro.public.v1+json','Content-Type: application/vnd.allegro.public.v1+json','Authorization: Bearer '.$_COOKIE['tokenn'],'Api-Key: c77d0744d4'];
	$curl = curl_init($uri);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$data = json_encode($params);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);   
	return curl_exec($curl);
}

function putPublic($uri, array $params = []) {
	$headers = ['Accept: application/vnd.allegro.public.v1+json','Content-Type: application/vnd.allegro.public.v1+json','Authorization: Bearer '.$_COOKIE['tokenn'],'Api-Key: c77d0744d4'];
	$curl = curl_init($uri);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$data = json_encode($params);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);   
	return curl_exec($curl);
}

function patch($uri, array $params = []) {
	$headers = ['Accept: application/vnd.allegro.beta.v3+json','Content-Type: application/vnd.allegro.beta.v3+json','Authorization: Bearer '.$_COOKIE['tokenn'],'Api-Key: c77d0744d4'];
	$curl = curl_init($uri);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$data = json_encode($params);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);   
	return curl_exec($curl);
}

function uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

function gettaxid($id){
    $i = getRequestPublic('https://api.allegro.pl/sale/tax-settings?category.id='.$id);
    $i = json_decode($i);
    $wybieram = '';
    if (count($i->settings) > 1){
        foreach($i->settings as $tax){
            if(!isset($tax->exemption->id) && (!isset($tax->subject->id) || $tax->subject->id == "GOODS") && (!isset($tax->rate->id) || $tax->rate->id == "23.00")){
                if ((!isset($wybieram->rate->id) && isset($tax->rate->id)) || (!isset($wybieram->subject->id) && isset($tax->subject->id))){
                    $wybieram = $tax;
                }
            }
        }
    }
    return $wybieram;
}

function getalloffers($status="ACTIVE"){
	$status = ($status != "ALL")?'&publication.status='.$status:'';
	$alloffers = array();
	$i = 0;
	$limit = 1000;
	while (true){
		$offset = $i*$limit;
		$aukcje = getRequestPublic('https://api.allegro.pl/sale/offers?limit='.$limit.$status.'&offset='.$offset);
		$aukcje = json_decode($aukcje);
		if($aukcje->totalCount < $offset){break;}
		$alloffers = array_merge($alloffers,$aukcje->offers);
		$i++;
	}
	return $alloffers;
}

function getShipping($id=""){
	$i = getRequestPublic('https://api.allegro.pl/sale/shipping-rates');
	$i = json_decode($i);
	$re = '';
	if ($id == ''){
		$re = $i->shippingRates;
	}else{
		foreach ($i->shippingRates as $ship){
			if ($ship->id == $id){
				$re = $ship->name;
				break;
			}
		}
	}
	return $re;
}
?>