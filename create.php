<?php
include_once("../database.class.php");
$pdo = new DBconn();

$pdo->query('CREATE TABLE newallegroorders (
    fod VARCHAR(38) NOT NULL UNIQUE,
    messagetoseller INT(1) NOT NULL,
    buyerlogin VARCHAR(50) NOT NULL,
    statusfod VARCHAR(22) NOT NULL,
    paymentid VARCHAR(38) NULL,
    paymenttype VARCHAR(16) NULL,
    paymentprovider VARCHAR(20) NULL,
    paymentfinished DATETIME NULL,
    paymentpaid FLOAT NULL,
    itemid TEXT NOT NULL,
    deliverymethod VARCHAR(100) NULL,
    summary FLOAT NOT NULL,
    discounts  VARCHAR(20) NULL,
    transactionid BIGINT(11) NULL,
    bougthtime DATETIME NULL,
    filledintime DATETIME NULL,
    readytime DATETIME NULL,
    shipmentsnumber VARCHAR(60) NULL,
    shipmenttime DATETIME NULL
    )');

$pdo->query('CREATE TABLE newallegromessage (
    fod VARCHAR(38) NOT NULL UNIQUE,
    messagetoseller TEXT NOT NULL
    )');

$pdo->query('CREATE TABLE newallegrobuyer (
    fod VARCHAR(38) NOT NULL UNIQUE,
    userid INT(12) NOT NULL,
    email VARCHAR(100) NOT NULL,
    username VARCHAR(100) NULL,
    personalIdentity INT(11) NULL,
    phoneNumber VARCHAR(20) NULL,
    street VARCHAR(100) NULL,
    city VARCHAR(100) NULL,
    postcode VARCHAR(7) NULL
    )');

$pdo->query('CREATE TABLE newallegrodelivery (
    fod VARCHAR(38) NOT NULL UNIQUE,
    addressname VARCHAR(100) NOT NULL,
    street VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    postcode VARCHAR(7) NOT NULL,
    companyname VARCHAR(100) NULL,
    phonenumber VARCHAR(20) NULL,
    methodid VARCHAR(38) NOT NULL,
    methodname VARCHAR(100) NOT NULL,
    pickuppoint VARCHAR(150) NULL,
    cost FLOAT NOT NULL,
    smart INT(1) NULL,
    numberofpackages INT(2) NULL
    )');

$pdo->query('CREATE TABLE newallegroinvoice (
    fod VARCHAR(38) NOT NULL UNIQUE,
    street VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    zipcode VARCHAR(7) NOT NULL,
    companyname VARCHAR(100) NULL,
    companytaxid VARCHAR(15) NULL,
    naturalperson VARCHAR(38) NULL
    )');

$pdo->query('CREATE TABLE newallegrolineitems (
    id VARCHAR(38) NOT NULL UNIQUE,
    fod VARCHAR(38) NULL,
    offerid VARCHAR(15) NOT NULL,
    offername VARCHAR(60) NOT NULL,
    offerexternal VARCHAR(60) NULL,
    quantity VARCHAR(10) NOT NULL,
    originalprice FLOAT NOT NULL,
    price FLOAT NOT NULL,
    boughtat DATETIME NOT NULL
    )');

$pdo->query('CREATE TABLE newallegrosurcharges (
    id VARCHAR(38) NOT NULL UNIQUE,
    fod VARCHAR(38) NOT NULL,
    transactionid INT(12) NULL,
    methodtype VARCHAR(30) NOT NULL,
    methodprovider VARCHAR(30) NOT NULL,
    finishedat DATETIME NULL,
    price FLOAT NULL
    )');
