<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Allegro iSAT PayU</title>
	<meta name="author" content="Maciej Krupiński">
	<link rel="stylesheet" href="style.css">
</head>
<style>
table{display:inline;}
#monit{padding-left:100px;}
</style>
<body>
<nav>
<li><a href="../index.php"><img src="../img/home1.png"></a></li>
<li style="float:right;"><a href="../billing/index.php">Zwroty</a></li>
</nav>
<?php
function dmr($str){return date('d-m-Y H:i',$str);}

$pdo = new PDO('mysql:host=localhost;dbname=satserwis','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$options['features'] = SOAP_SINGLE_ELEMENT_ARRAYS;
try {$soapClient = new SoapClient('https://webapi.allegro.pl/service.php?wsdl', $options);$request = array('countryId' => 1,'webapiKey' => 'c77d0744d4');$result = $soapClient->doQueryAllSysStatus($request);$versionKeys = array();foreach ($result->sysCountryStatus->item as $row) {$versionKeys[$row->countryId] = $row;}$request = array('userLogin' => 'isat','userHashPassword' => base64_encode(hash('sha256', 'Radek72335!', true)),'countryCode' => 1,'webapiKey' => 'c77d0744d4','localVersion' => $versionKeys[1]->verKey,);$session = $soapClient->doLoginEnc($request);}catch(Exception $e){echo $e;}

$dogetmypayouts_request = array(
    'sessionHandle' => $session->sessionHandlePart,
    'transCreateDateFrom' => strtotime("-20 day"),
    'transCreateDateTo' => time(),
    'transPageLimit' => 40,
    'transOffset' => 0
);
$mylistpo = $soapClient->doGetMyPayouts($dogetmypayouts_request);
$lista = $mylistpo->payTransPayout->item;
echo '<table>';
foreach($lista as $pay){
    echo '<tr><td>'.$pay->payTransId.'</td><td>';
    echo ($pay->payTransRecvDate!="-1")?date('Y-m-d',$pay->payTransRecvDate):'';
    echo '</td><td>';
    echo ($pay->payTransRecvDate!="-1")?number_format($pay->payTransAmount,2,","," "):'';
    echo '</td><td>';
    echo ($pay->payTransStatus=="Zakończona")?'<a href="pdf.php?numer='.$pay->payTransId.'&data='.$pay->payTransRecvDate.'&suma='.$pay->payTransAmount.'">POBIERZ</a>':'';
    echo '</td><td><span onmouseout="usun()" onmouseover="look(\''.$pay->payTransId.'\')">&#x1f441</span></td></tr>';
}
echo '</table>';
echo '<table id="monit"></table>';
?>
</body>
</html>    
<script>
function usun(){
    document.getElementById("monit").remove();
}
function look(pid){
    var table = document.createElement("table");
    document.getElementsByTagName("body")[0].appendChild(table);
    document.getElementsByTagName("table")[1].setAttribute("id", "monit");
    var monit = document.getElementById('monit');
	if (window.XMLHttpRequest) {xmlhttp=new XMLHttpRequest();} else {xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			var odpowiedz = JSON.parse(xmlhttp.responseText);
			console.log(odpowiedz);
            for(i=0;i<odpowiedz.length;i++){
                var tr = document.createElement("tr");
                var tda = document.createElement("td");
                var tdb = document.createElement("td");
                var tdc = document.createElement("td");
                var id = document.createTextNode(odpowiedz[i].tranasctionId);
                var kwota = document.createTextNode(odpowiedz[i].totalAmount);
                var name = document.createTextNode(odpowiedz[i].userName);
                tda.appendChild(id);
                tr.appendChild(tda);
                tdb.appendChild(kwota);
                tr.appendChild(tdb);
                tdc.appendChild(name);
                tr.appendChild(tdc);
                document.getElementById("monit").appendChild(tr);
            }
		}
	}
	var url = "&pid="+pid;
	xmlhttp.open("POST","pid.php",true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Connection", "close");
	xmlhttp.send(url);
}
</script>