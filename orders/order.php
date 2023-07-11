<html>

<head>
	<meta charset="UTF-8">
	<title>Allegro zamówienie</title>
	<meta name="author" content="Maciej Krupiński">
	<link rel="stylesheet" href="style.css">
</head>

<body onload="items()">
	<?php
	include_once("../allegrofunction.php");

	include_once("./returnsInfo.php");
	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');

	$pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4', 'root', '');
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	if (isset($_GET['fod'])) $numer_fod = $_GET['fod'];

	if (!isset($numer_fod) && isset($_GET['paymentid'])) {
		$order = $pdo->prepare('SELECT fod FROM newallegroorders WHERE paymentid=:paymentid');
		$order->bindValue(":paymentid", $_GET['paymentid'], PDO::PARAM_STR);
		$order->execute();
		$order = $order->fetch();
		$numer_fod = $order['fod'];
	}

	$allegrofod = getRequestPublic('https://api.allegro.pl/order/checkout-forms/' . $numer_fod);
	$allegrofod = json_decode($allegrofod);

	/*$shipments = getRequestPublic('https://api.allegro.pl/order/checkout-forms/' . $numer_fod . '/shipments');
	$shipments = json_decode($shipments);
	$shipmentsInfo = new Returns($shipments->shipments[0]);*/

	$orderbilling = getRequestPublic('https://api.allegro.pl/billing/billing-entries?order.id=' . $numer_fod);
	$orderbilling = json_decode($orderbilling);

	/*echo '<pre>';
	print_r($shipmentsInfo);
	echo '</pre>';*/

	function zwrot($orderid)
	{
		$res = getRequest('https://api.allegro.pl/order/customer-returns?orderId=' . $orderid);
		$res = json_decode($res);
		return $res->count;
	}

	$order = $pdo->prepare('SELECT * FROM newallegroorders WHERE fod=:fod');
	$order->bindValue(":fod", $numer_fod, PDO::PARAM_STR);
	$order->execute();
	$order = $order->fetch();

	$buyer = $pdo->prepare('SELECT * FROM newallegrobuyer WHERE fod=:fod');
	$buyer->bindValue(":fod", $numer_fod, PDO::PARAM_STR);
	$buyer->execute();
	$buyer = $buyer->fetch();

	$kupujacy = $buyer['username'] . '</p><p>' . $buyer['street'] . '</p><p>' . $buyer['postcode'] . ' ' . $buyer['city'] . '</p><p>' . $buyer['phoneNumber'];

	$invoice = $pdo->prepare('SELECT * FROM newallegroinvoice WHERE fod=:fod');
	$invoice->bindValue(":fod", $numer_fod, PDO::PARAM_STR);
	$invoice->execute();
	$faktura = '';
	if ($invoice->rowCount() != 0) {
		$invoice = $invoice->fetch();
		if ($invoice['companytaxid'] != "") {
			$faktura .= '<b>NIP: ' . $invoice['companytaxid'] . '</b></b><p>';
		}
		$faktura .= $invoice['companyname'] . '</p><p>' . $invoice['naturalperson'] . '</p><p>' . $invoice['street'] . '</p><p>' . $invoice['zipcode'] . ' ' . $invoice['city'];
	} else {
		if ($allegrofod->invoice->required == NULL) {
			$faktura .= 'PARAGON';
		} else {
			header("Location: ./faktura.php?fod=" . $order['fod']);
		}
	}

	$delivery = $pdo->prepare('SELECT * FROM newallegrodelivery WHERE fod=:fod');
	$delivery->bindValue(":fod", $numer_fod, PDO::PARAM_STR);
	$delivery->execute();
	if ($delivery->rowCount() > 0) {
		$delivery = $delivery->fetch();
		$wysylka = $delivery['companyname'] . '</p><p>' . $delivery['addressname'] . '</p><p>' . $delivery['street'] . '</p><p>' . $delivery['postcode'] . ' ' . $delivery['city'] . '</p><p>' . $delivery['phonenumber'];
		$methodname = $delivery['methodname'];
		$methodname .= ($delivery['smart'] != 0) ? ' <b>SMART</b>' : '';
		$pickuppoint = $delivery['pickuppoint'];
		$deliverycost = number_format($delivery['cost'], 2, ',', ' ');
		$numerpaczki = $delivery['numberofpackages'];
	} else {
		($order['statusfod'] == "READY_FOR_PROCESSING") ? header("Location: ./odswiez.php?fod=" . $order['fod']) : '';
		$wysylka = '';
		$methodname = '';
		$pickuppoint = '';
		$deliverycost = '';
		$numerpaczki = '';
	}

	$message = $pdo->prepare('SELECT * FROM newallegromessage WHERE fod=:fod');
	$message->bindValue(":fod", $numer_fod, PDO::PARAM_STR);
	$message->execute();
	$message = ($message->rowcount() > 0) ? $message->fetch()['messagetoseller'] : '';

	$surcharges = $pdo->prepare('SELECT * FROM newallegrosurcharges WHERE fod=:fod');
	$surcharges->bindValue(":fod", $numer_fod, PDO::PARAM_STR);
	$surcharges->execute();
	$surcharges = $surcharges->fetch();

	$zakupy = $pdo->prepare('SELECT * FROM newallegrolineitems WHERE FIND_IN_SET(id,:itemid)');
	$zakupy->bindValue(":itemid", $order['itemid']);
	$zakupy->execute();
	$towary = '';
	$zatowary = 0;
	$lp = 0;
	foreach ($zakupy->fetchAll() as $i) {
		$zatowary += $i['price'] * $i['quantity'];
		$towary .= '<tr class="item"><td>' . ++$lp . '</td><td><a target="_blank" href="https://allegro.pl/oferta/' . $i['offerid'] . '">(' . $i['offerid'] . ')</a></td><td class="offername">' . $i['offername'] . '</td><td class="offerexternal">' . $i['offerexternal'] . '</td><td class="quantity">' . $i['quantity'] . '</td><td>' . number_format($i['originalprice'], 2, ',', ' ') . '</td><td>' . number_format($i['price'], 2, ',', ' ') . '</td></tr>';
	}

	$korekta = '<a href="mailto:' . $buyer['email'] . '?subject=Korekta od iSAT (allegro), zwróciłeś &body=Dzień dobry,%0D%0Aw załączniku przesyłam korektę do FV. Proszę o mailowe potwierdzenie jej otrzymania.%0D%0A%0D%0AMaciej Krupiński%0D%0ASAT-SERWIS%0D%0Aul. Północna 36%0D%0A91-425 ŁÓDŹ%0D%0A426319277%0D%0APN-PT w godz. 9-17">@Korekta</a>';

	$prev = $pdo->query('SELECT fod FROM newallegroorders WHERE readytime<"' . $order['readytime'] . '" ORDER BY readytime DESC LIMIT 1');
	$prev = ($prev->rowCount() > 0) ? '<li><a class="klik" href="?fod=' . $prev->fetch()['fod'] . '">Poprzednie</a></li>' : '<li><a class="klik dis">Poprzednie</a></li>';
	$next = $pdo->query('SELECT fod FROM newallegroorders WHERE readytime>"' . $order['readytime'] . '" ORDER BY readytime ASC LIMIT 1');
	$next = ($next->rowCount() > 0) ? '<li><a id="nastepne" class="klik" href="?fod=' . $next->fetch()['fod'] . '">Następne</a></li>' : '<li><a id="nastepne" class="klik dis">Następne</a></li>';
	echo '<nav class="navt">
<li><a id="home" href="../index.php"><img src="../img/home1.png"></a></li>

<li><select id="statusfoda" onchange="if (this.selectedIndex) status(this.selectedIndex);">
<option value="FILLED_IN"';
	echo ($order['statusfod'] == "FILLED_IN") ? " selected" : "";
	echo '>Czekamy na wplate</option>
<option value="SENT"';
	echo ($order['statusfod'] == "SENT") ? " selected" : "";
	echo '>Wysłane</option>
<option value="CANCELLED"';
	echo ($order['statusfod'] == "CANCELLED") ? " selected" : "";
	echo '>Anulowane</option>
<option value="READY_FOR_PROCESSING"';
	echo ($order['statusfod'] == "READY_FOR_PROCESSING") ? " selected" : "";
	echo '>Nowe</option>
<option value="COMPLETING"';
	echo ($order['statusfod'] == "COMPLETING") ? " selected" : "";
	echo '>Przyjete</option>
<option hidden value="PRINT">Drukowanie</option>
</select></li>

<li><a id="drukuj" class="klik';
	echo ($order['statusfod'] == "CANCELLED") ? ' dis' : '';
	echo '" onclick="status(5)">Drukuj</a></li>

' . $next . $prev . '
</nav>';
	echo '<div id="komunikat" class="sektor"></div>';
	echo '<div class="sektor">';
	echo '<span id="orderName" class="nazwa" onmouseover="showhide(\'orderName\',\'orderBtn\')">Zamówienie:</span><button id="orderBtn" class="btn" onclick="edit(\'order\')" onmouseout="showhide(\'orderBtn\',\'orderName\')">Edytuj dane</button><span class="wartosc" style="font-size:22px;">' . $order['transactionid'] . '</span></br>';
	echo '<span class="nazwa"></span><span id="numerfod" class="wartosc" style="font-size:12px;">' . $order['fod'] . '</span></br>';
	echo '<span class="nazwa">Daty:</span><span class="wartosc">kupno - ' . $order['bougthtime'] . ', wybór - ' . $order['filledintime'] . ', zatwierdzenie - ' . $order['readytime'] . '</span></br>';
	echo '<span class="nazwa">e-mail:</span><span class="wartosc">' . $buyer['email'] . '</span>' . $korekta . '</br>';
	echo '<span class="nazwa">Login:</span><span class="wartosc" style="font-size:20px;">' . $order['buyerlogin'] . '</span>';
	echo '</div>';

	echo '<div class="sektor">';
	echo '<div class="box"><p id="buyerName" onmouseover="showhide(\'buyerName\',\'buyerBtn\')">Dane klienta</p><button id="buyerBtn" class="btn" onmouseout="showhide(\'buyerBtn\',\'buyerName\')" onclick="edit(\'buyer\')">Edytuj dane</button><p>' . $kupujacy . '</p></div>';
	echo '<div class="box"><p id="invoiceName" onmouseover="showhide(\'invoiceName\',\'invoiceBtn\')">Dane bilingowe</p><button id="invoiceBtn" class="btn" onmouseout="showhide(\'invoiceBtn\',\'invoiceName\')" onclick="edit(\'invoice\')">Edytuj dane</button><p>' . $faktura . '</p></div>';
	echo '<div class="box"><p id="deliveryName" onmouseover="showhide(\'deliveryName\',\'deliveryBtn\')">Dane dostawy</p><button id="deliveryBtn" class="btn" onmouseout="showhide(\'deliveryBtn\',\'deliveryName\')" onclick="edit(\'delivery\')">Edytuj dane</button><p>' . $wysylka . '</p></div>';
	echo '</div>';

	echo '<div class="sektor">';
	echo '<table id="itemlist" >';
	echo '<tr ondblclick="edit(\'items\')"><td></td><td>Oferta</td><td>Nazwa</td><td>Kod</td><td>Ilość</td><td>Cena</td><td>Zapłacił</td></tr>';
	echo $towary;
	echo '<tr><td colspan="6">Łącznie:</td><td>' . number_format($zatowary, 2, ',', ' ') . '</td></tr>';
	echo '</table>';
	echo '</div>';

	echo '<div class="sektor">';
	echo '<span class="nazwa">Forma dostawy:</span><span class="wartosc">' . $methodname . '</span></br>';
	echo '<span class="nazwa">Punkt odbioru:</span><span class="wartosc">' . $pickuppoint . '</span></br>';
	echo '<span class="nazwa">Koszt dostawy:</span><span class="wartosc">' . $deliverycost . '</span></br>';
	echo '<span class="nazwa">Ilość paczek:</span><span class="wartosc">' . $numerpaczki . '</span></br>';
	echo ($order['shipmentsnumber'] != NULL) ? '<span class="nazwa color_red">Numer i data wysyłki:</span><span class="wartosc">' . $order['shipmentsnumber'] . ' w dniu: ' . $order['shipmenttime'] . '</span>' : '';
	echo '</div>';

	echo '<div class="sektor">';
	echo '<span class="nazwa">Sposób płatności:</span><span class="wartosc">';
	echo ($order['paymenttype'] == "CASH_ON_DELIVERY") ? "Pobranie" : "";
	echo ($order['paymenttype'] == "ONLINE") ? $order['paymentprovider'] : "";
	echo '</span></br>';
	echo ($order['paymenttype'] != "CASH_ON_DELIVERY") ? '<span class="nazwa">Data dokonania płatności:</span><span class="wartosc">' . $order['paymentfinished'] . '</span></br>' : '';
	echo '<span class="nazwa">Id płatności:</span><span class="wartosc"><a target="_blank" href="https://allegro.pl/myaccount/newpayments_payment_details.php?trans_id=' . $order['transactionid'] . '">' . $order['paymentid'] . '</a></span>';
	echo '</div>';

	if (!empty($surcharges)) {
		echo '<div class="sektor">';
		echo '<span class="nazwa">DOPŁATA:</span><span class="wartosc"></span></br>';
		echo '<span class="nazwa">Kwota:</span><span class="wartosc">' . number_format($surcharges['price'], 2, ',', ' ') . '</span></br>';
		echo '<span class="nazwa">Sposód dopłaty:</span><span class="wartosc">' . $surcharges['methodprovider'] . '</span></br>';
		echo '<span class="nazwa">Data płatności:</span><span class="wartosc">' . $surcharges['finishedat'] . '</span></br>';
		echo '<span class="nazwa">Id płatności:</span><span class="wartosc"><a target="_blank" href="https://allegro.pl/myaccount/newpayments_payment_details.php?trans_id=' . $surcharges['id'] . '">' . $surcharges['id'] . '</a> (' . $surcharges['transactionid'] . ')</span>';
		echo '</div>';
	}

	echo '<div class="sektor">';
	echo '<span class="nazwa"><input checked type="checkbox" id="message" name="message">Uwagi do zamówienia:</span><span class="wartosc">' . $message . '</span></br>';
	echo '</div>';
	echo '<nav class="navb">
<li>';
	if (zwrot($numer_fod) > 0) {
		echo '<a href="pdfzwrot.php?fod=' . $numer_fod . '" class="klik">Dokument zwrotu</a>';
	}
	$deletefod = '<a class="klik" href="delete.php?fod=' . $numer_fod . '")">Usuń</a>';
	echo '<a onclick="surcharges(\'' . $numer_fod . '\')" class="klik">Odśwież</a></li>
</nav>';
	echo '<div id="fpp">';
	echo '<div id="fpp">';
	?>

	<pre>
<?php
$totalcost = 0.0;
foreach ($orderbilling->billingEntries as $orderCost) {
	if ($orderCost->type->id == 'SUC') {
		print_r(array($orderCost->type->name, $orderCost->offer->id, @$orderCost->offer->name, $orderCost->value->amount));
	} else {
		print_r(array($orderCost->type->name, $orderCost->value->amount));
	}
	$totalcost -= $orderCost->value->amount;
}
print_r("total $totalcost");
?>
</pre>
</body>

</html>

<script type="text/javascript">
	function showhide(ukryj, pokaz) {
		document.getElementById(ukryj).style.display = "none";
		document.getElementById(pokaz).style.display = "inline-block";
	}

	function edit(co) {
		var co;
		var numerfod = document.getElementById('numerfod').innerText;
		window.location.href = 'create.php?fod=' + numerfod + '&co=' + co;
	}

	function items() {
		const items = document.getElementsByClassName("item");
		const fpp = document.getElementById("fpp");
		for (let i = 0; i < items.length; i++) {
			const item = items[i];
			const externalid = item.getElementsByClassName("offerexternal")[0].innerText;
			const quantity = item.getElementsByClassName("quantity")[0].innerText;
			itemVerify(item, externalid, quantity);
		}
	}

	async function itemVerify(item, externalid, quantity) {
		let name = item.getElementsByClassName("offername")[0];
		const res = await fetch("./itemverify.php", {
			method: "POST",
			body: JSON.stringify({
				externalid: externalid,
			})
		});
		const data = await res.json();
		data.forEach(item => {
			var opt = document.createElement("p");
			opt.innerText = item.kodn + ' -> ' + item.nazwa + ' ( ' + item.ilosc + ' )';
			if (item.ilosc < quantity) {
				opt.setAttribute("style", "color: red;")
				name.setAttribute("style", "color: red;");
			}
			fpp.appendChild(opt);
		});
	}

	async function surcharges(nrfod) {
		/*const res = await fetch("./odswiez.php?"+new URLSearchParams({'fod': nrfod}), {
        method: "POST",
        headers: {'Content-Type': 'application/x-www-form-url-encoded', 'Accept': 'application/json'}
    });
	const data = await res.json();
	console.log(data);*/
		//window.location.href = 'order.php?fod='+nrfod;

		if (window.XMLHttpRequest) {
			xmlhttp = new XMLHttpRequest();
		} else {
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				var odpowiedz = xmlhttp.responseText;
				alert(odpowiedz);
				window.location.href = 'order.php?fod=' + nrfod;
			}
		}
		var url = "&fod=" + nrfod;
		xmlhttp.open("POST", "odswiez.php", true);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xmlhttp.send(url);
	}

	function status(indexselect) {
		var komunikat = document.getElementById('komunikat');
		var statusfodslect = document.getElementById('statusfoda');
		var numerfod = document.getElementById('numerfod').innerText;
		var pozwolenie = 1;
		if (indexselect == 5 & statusfodslect.selectedIndex == 2) {
			alert('Zmień status na inny aby wydrukować!');
			pozwolenie = 0;
		}
		if (pozwolenie != 0) {
			if (window.XMLHttpRequest) {
				xmlhttp = new XMLHttpRequest();
			} else {
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			}
			xmlhttp.onreadystatechange = function() {
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
					var odpowiedz = xmlhttp.responseText;
					console.log(odpowiedz)
					if (odpowiedz == 5) {
						odpowiedz = statusfodslect.selectedIndex;
					}
					console.log(odpowiedz)
					komunikat.style.display = "block";
					komunikat.style.height = "22px";
					komunikat.innerText = "Zmieniłeś status na " + statusfodslect[odpowiedz].innerText;
					if (indexselect == 5) {
						statusfodslect.selectedIndex = odpowiedz;
						print(numerfod);
					}
					if (statusfodslect.selectedIndex == 2) {
						document.getElementById('drukuj').classList.add("dis");
					} else {
						document.getElementById('drukuj').classList.remove("dis");
					}

				}
			}
			var url = "&status=" + numerfod + '//' + statusfodslect[indexselect].value + '//' + indexselect;
			xmlhttp.open("POST", "aktualizuj.php", true);
			xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xmlhttp.send(url);
		}
	}

	function print(nrfod) {
		var message = document.getElementById('message').checked;
		window.open("pdf.php?fod=" + nrfod + '&message=' + message, "_blank");
		window.focus();
	}
</script>