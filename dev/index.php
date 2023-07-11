<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Allegro zamówienia JS</title>
	<meta name="author" content="Maciej Krupiński">
	<!--<link rel="stylesheet" href="./css/style.css">
	<link rel="stylesheet" href="./css/stylenavi.css">-->
    <script src="scripts/orders.js"></script>
</head>
<body id="body" onload="getorders()">
    <nav>
        <ul>
            <li>Home</li>
            <li>Get new</li>
            <li>Search</li>
            <li>Other</li>
        </ul>
        <ul>
            <li>PayU</li>
            <li>DPD</li>
            <li>Aktualizuj</li>
        </ul>
    </nav>
    <div class="orders">
        <div class="orders-wait">
        </div>
        <div class="orders-canceled">
        </div>
    </div>
    <div class="orders">
    </div>
    <footer>Allegro zamówienia</footer>
</body>


<template id="order-ready">
    <li class="order">
        <a href="">
            <span class="order-data"></span>
            <span class="login-user"></span>
            <span class="name-user"></span>
            <span class="delivery-name"></span>
            <span class="summary"></span>
            <span class="order-id"></span>
            <span class="order-item-count"></span>
        </a>
    </li>
</template>

<template id="order-wait">
    <a class="order" href="">
        <span class="order-data"></span>
        <span class="login-user"></span>
        <span class="name-user"></span>
    </a>
</template>

</html>