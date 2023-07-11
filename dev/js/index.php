<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Allegro zamówienia</title>
	<meta name="author" content="Maciej Krupiński">
	
    <script src="showorders.js"></script>
</head>
<body id="body" onload="getorders()">
    <nav>
        <ul>
            <li>Home</li>
            <li>Search</li>
            <li>Get new</li>
            <li>Other</li>
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

            <span class="order-data"></span>
            <span class="login-user"></span>
            <span class="name-user"></span>
            <span class="delivery-name"></span>
            <span class="summary"></span>
            <span class="order-id"></span>
            <span class="order-item-count"></span>

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