<?php
class AllegroOAuth2Client {
	protected $providerSettings = [
		'ClientId' => 'e365c9da3dd84fa8bf604b180c293e5d',
		'ClientSecret' => 'nQgtikwhynuKzmhFnwLHUTdWsoSmt1Ggn8lVZjdkJy5sfYLpMwsvIDDc3e1jShTO',
		'ApiKey' => 'c77d0744d4',
		'RedirectUri' => 'http://sat.pl/newallegro/',
		'AuthorizationUri' => 'https://allegro.pl/auth/oauth/authorize',
		'TokenUri' => 'https://allegro.pl/auth/oauth/token'
	];

	protected $headers = [
		'Content-Type: application/x-www-form-urlencoded'
	];

	public function __construct(array $customSettings = []) {
		$this->providerSettings = array_merge(
			$this->providerSettings, 
			$customSettings
		);
		$this->headers[] = 'Authorization: Basic '. base64_encode($this->providerSettings['ClientId'] . ':' . $this->providerSettings['ClientSecret']);
	}

	public function tokenRequest($code) {
		$curl = curl_init($this->providerSettings['TokenUri']);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['grant_type' => 'authorization_code','code' => $code,'api-key' => $this->providerSettings['ApiKey'],'redirect_uri' => $this->providerSettings['RedirectUri']]));    
		$result = ($result = curl_exec($curl)) === false ? false : json_decode($result);
		if ($result === false) { throw new Exception('Unrecognized error'); }
		else if (!empty($result->error)) {throw new Exception($result->error . ' - ' . $result->error_description);}
		else {return $result;}
	}

	public function getAuthorizationUri() {
		return $this->providerSettings['AuthorizationUri'] . '?' . http_build_query(['response_type' => 'code','client_id' => $this->providerSettings['ClientId'],'api-key' => $this->providerSettings['ApiKey'],'redirect_uri' => $this->providerSettings['RedirectUri']]);
	}
}

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
}

function checkOrders($params = array()){
	$other = '';
	foreach ($params as $k => $val) {
		if (is_array($val)){
			foreach ($val as $v){
				$other .= $k.'='.$v.'&';
			}
		}else{
			$other .= $k.'='.$val.'&';
		}
	}
	$ordersnew = getRequestPublic('https://api.allegro.pl/order/checkout-forms?'.substr($other,0,-1));
	return json_decode($ordersnew);
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