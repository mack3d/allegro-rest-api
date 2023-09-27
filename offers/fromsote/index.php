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

	body {
		margin: 0px auto;
		display: block;
		width: 95%;
		font-family: "Lucida Console";
	}

	div#contener,
	div#lookshort,
	div#lookdiv {
		width: 100%;
		height: 100%;
		padding: 10px;
		background: #efefef;
		position: relative;
		display: block;
		overflow: auto;
	}

	div#lookshort,
	div#lookdiv {
		display: block;
		font-size: 14px;
		width: 505px;
	}

	div#col1,
	div#col2 {
		display: inline-block;
		position: relative;
		float: left;
		padding: 10px;
	}

	div#parametry {
		display: block;
		width: 505px;
		min-height: 5px;
		background-color: none;
		border: 1px solid gray;
	}

	input,
	textarea {
		position: relative;
		display: block;
		margin: 5px 0px;
		font-family: "Lucida Console";
	}

	textarea {
		resize: vertical;
	}

	input.dane,
	input.name {
		width: 500px;
	}

	input.namelen {
		font-weight: bold;
		border: none;
	}

	input.namelen,
	input.name {
		display: inline-block;
	}

	input#code {
		text-align: center;
		float: left;
	}

	a {
		background: #efefef;
		padding: 5px 40px;
		border: 1px solid orange;
		border-radius: 1px;
		text-decoration: none;
		color: #000;
	}
</style>

<?php
if (!isset($_COOKIE['sklep'])) {
	$sesja = new SoapClient('https://sklep.satserwis.pl/backend.php/webapi/soap?wsdl');
	$log = new stdClass();
	$log->username = getenv('SOU');
	$log->password = getenv('SOP');
	setcookie('sklep', $sesja->doLogin($log)->hash, time() + 1800);
}
$kod = '';
@$kod = $_GET['kod'];
?>

<body onLoad="getProductFromSote()">
	<div id="contener">
		<div id="col1">
			<?php echo '<input type="text" id="code" placeholder="Podaj kod" onKeyUp="getProductFromSote()" value="' . $kod . '">'; ?>
		</div>
		<div id="col2">
			<input class="name" type="text" id="names" onKeyUp="liczbaznakow()" placeholder="Nazwa przedmiotu">
			<input class="namelen" type="text" id="liczbaznakow" disabled="disabled">
			<input class="dane" type="text" id="nameallegro" onDblClick="zmiennazwe()" placeholder="Ostatnia nazwa przedmiotu">
			<input class="dane" type="text" id="man_code" placeholder="EAN" onchange="getproduct(this)">
			<input class="dane" type="text" id="price" placeholder="Cena">
			<input class="dane" type="text" id="stock" placeholder="Ilość">
			<input class="dane" type="text" id="allegrocategory" placeholder="kategoria" value="67346" onChange="allegrocategoryparametry()">
			<input class="dane" type="text" id="allegrocategoryname" placeholder="kategoria" disabled="disabled">

			<textarea id="short_description" placeholder="Skrócony opis" rows="3" cols="70" onKeyUp="podglad()"></textarea>
			<textarea id="description" placeholder="Opis" rows="10" cols="70" onKeyUp="podglad()"></textarea>
			<input class="dane" type="id" id="id" disabled>
			<input class="dane" type="Button" value="Dodaj" onClick="createDraft()">
			<br>
			<div id="numbera"></div>
		</div>
		<div id="lookshort">
		</div>
		<div id="lookdiv">
		</div>
	</div>
</body>


<script src="scripts.js"></script>