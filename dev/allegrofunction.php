<?php
class Allegro{
	function __construct(){
		$this->url = "https://api.allegro.pl";
		$this->access_token = $_COOKIE['tokenn'];
	}

	public function order($method = 'GET', $endpoint = '/checkout-forms', array $params = []){
		$endpoint = $this->url.'/order'.$endpoint;
		return $this->connect($method, $endpoint, $params);
	}

	public function sale($method = 'GET', $endpoint = '/offers', array $params = []){
		$endpoint = $this->url.'/sale'.$endpoint;
		return $this->connect($method, $endpoint, $params);
	}

	public function alloffers(){
		$all = array();
		$offers = $this->sale('GET', "/offers?limit=1000&offset=0");
		$all = array_merge($all, $offers->offers);
		for ($i = 1000; $i < $offers->totalCount; $i += 1000){
			$offers = $this->sale('GET', "/offers?limit=1000&offset={$i}");
			$all = array_merge($all, $offers->offers);
		}
		return json_decode(json_encode(array('offers' => $all, 'totalCount' => count($all))));
	}

	public function connect($method, $url, $params) {
		$headers = ['Accept: application/vnd.allegro.public.v1+json','Authorization: Bearer '.$this->access_token];
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$data = json_encode($params);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);   
		return json_decode(curl_exec($curl));
	}

	public function uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
}

function allegro($method, $endpoint, array $params = []) {
	$url = "https://api.allegro.pl{$endpoint}";
	$headers = ['Accept: application/vnd.allegro.public.v1+json','Content-Type: application/vnd.allegro.public.v1+json','Authorization: Bearer '.$_COOKIE['tokenn'],'Api-Key: c77d0744d4'];
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
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