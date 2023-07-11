<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Nowa aukcja z sote</title>
	<meta name="author" content="Maciej Krupiński">
</head>

<style>
html {
    height: 100%;
    margin: 0;
    padding: 0;
}
body{
	margin: 0px auto;
	display: block;
	width: 95%;
	font-family: "Lucida Console";
}
div#contener, div#lookshort, div#lookdiv{
	width: 100%;
	height: 100%;
	padding: 10px;
	background: #efefef;
	position: relative;
	display: block;
    overflow: auto;
}
div#lookshort, div#lookdiv{
	font-size: 14px;
}

div#col1, div#col2{
	display: inline-block;
	position: relative;
	float: left;
	padding: 10px;
}
div#parametry{
	display:block;
	width:505px;
	min-height:5px;
	background-color: none;
	border: 1px solid gray;
}
input,textarea{
	position: relative;
	display: block;
	margin: 5px 0px;
	font-family: "Lucida Console";
}
textarea{
	resize:vertical;
}
input.dane,input.name{
	width: 500px;
}
input.namelen{
	font-weight: bold;
	border: none;
}
input.namelen,input.name{
	display: inline-block;
}
input#code{
	text-align: center;
	float: left;
}
a{
	background: #efefef;
	padding: 5px 40px;
	border: 1px solid orange;
	border-radius: 1px;
	text-decoration: none;
	color: #000;
}
</style>

<?php
//*******************************************
class AllegroOAuth2Client {
	protected $providerSettings = ['ClientId' => 'e365c9da3dd84fa8bf604b180c293e5d','ClientSecret' => 'nQgtikwhynuKzmhFnwLHUTdWsoSmt1Ggn8lVZjdkJy5sfYLpMwsvIDDc3e1jShTO','ApiKey' => 'c77d0744d4','RedirectUri' => 'http://localhost/newallegro/offers/createfromsote/','AuthorizationUri' => 'https://ssl.allegro.pl/auth/oauth/authorize','TokenUri' => 'https://ssl.allegro.pl/auth/oauth/token'];
	protected $headers = ['Content-Type: application/x-www-form-urlencoded'];
	public function __construct(array $customSettings = []) {$this->providerSettings = array_merge($this->providerSettings, $customSettings);$this->headers[] = 'Authorization: Basic '. base64_encode($this->providerSettings['ClientId'] . ':' . $this->providerSettings['ClientSecret']);}
	public function tokenRequest($code) {
	$curl = curl_init($this->providerSettings['TokenUri']);curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);curl_setopt($curl, CURLOPT_POST, true);curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['grant_type' => 'authorization_code','code' => $code,'api-key' => $this->providerSettings['ApiKey'],'redirect_uri' => $this->providerSettings['RedirectUri']]));    
	$result = ($result = curl_exec($curl)) === false ? false : json_decode($result);
	if ($result === false) {throw new Exception('Unrecognized error');}
	else if (!empty($result->error)) {throw new Exception($result->error . ' - ' . $result->error_description);}
	else {return $result;}
	}
	public function getAuthorizationUri() {return $this->providerSettings['AuthorizationUri'] . '?' . http_build_query(['response_type' => 'code','client_id' => $this->providerSettings['ClientId'],'api-key' => $this->providerSettings['ApiKey'],'redirect_uri' => $this->providerSettings['RedirectUri']]);}
}
if(!isset($_COOKIE['tokenn'])){
	$auth = new AllegroOAuth2Client();
	echo '<a class="loguj" href="'.$auth->getAuthorizationUri().'">Zaloguj do Allegro</a>';
	if(!empty($_GET['code'])){
		$result = $auth->tokenRequest($_GET['code']);
		setcookie('tokenn',$result->access_token,time()+$result->expires_in);
		header("Location: ./");
	}
}
//*******************************************
/*
include_once("../../allegrofunction.php");
$info = getRequestPublic('https://api.allegro.pl/sale/offers/7959754181');
$info = json_decode($info);
print_r($info);
*/

if(!isset($_COOKIE['sklep'])){
    $sesja = new SoapClient('https://sklep.satserwis.pl/backend.php/webapi/soap?wsdl');
    $log = new stdClass();
    $log->username = "webapi@marketpol.pl";
    $log->password = "maciejek1";
    setcookie('sklep',$sesja->doLogin($log)->hash,time()+1800);
}
$kod = '';
@$kod = $_GET['kod'];
?>

<body onLoad="getProductFromSote()">
	<div id="contener">
		<div id="col1">
			<?php echo '<input type="text" id="code" placeholder="Podaj kod" onKeyUp="getProductFromSote()" value="'.$kod.'">'; ?>
		</div>
		<div id="col2">
			<input class="name" type="text" id="names" onKeyUp="liczbaznakow()" placeholder="Nazwa przedmiotu" >
			<input class="namelen" type="text" id="liczbaznakow" disabled="disabled">
			<input class="dane" type="text" id="nameallegro" onDblClick="zmiennazwe()" placeholder="Ostatnia nazwa przedmiotu">
			<input class="dane" type="text" id="man_code" placeholder="EAN" onchange="getproduct(this)">
			<input class="dane" type="text" id="price" placeholder="Cena">
			<input class="dane" type="text" id="stock" placeholder="Ilość">
			<input class="dane" type="text" id="allegrocategory" placeholder="kategoria" value="67346" onChange="allegrocategoryparametry()">
			<input class="dane" type="text" id="allegrocategoryname" placeholder="kategoria" disabled="disabled">
			<ul id="allegroparametry">
			
			</ul>
			<textarea id="short_description" placeholder="Skrócony opis" rows="3" cols="100" onKeyUp="podglad()"></textarea>
			<textarea id="description" placeholder="Opis" rows="10" cols="100" onKeyUp="podglad()"></textarea>
			<input class="dane" type="id" id="id" disabled>
			<input class="dane" type="Button" value="Dodaj" onClick="createDraft()">
			<br>
			<div id="numbera"></div>
		</div>
	</div>
	<div id="lookshort">
	</div>
	<div id="lookdiv">
	</div>
</body>


<script src="scripts.js"></script>