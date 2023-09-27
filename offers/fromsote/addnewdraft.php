<?php
include_once("../../allegrofunction.php");
$postdata = file_get_contents("php://input");
$getData = json_decode($postdata);

$code = $getData->code;
$names = $getData->names;
$short_description = $getData->short_description;
$description = $getData->description;
$price = $getData->price;
$man_code = $getData->man_code;
$id = $getData->id;
$allegrocategory = $getData->allegrocategory;
$stock = $getData->stock;

function postImage($image)
{
    $binary = file_get_contents($image);
    $headers = ['Accept: application/vnd.allegro.public.v1+json', 'Content-Type: image/jpeg', 'Authorization: Bearer ' . $_COOKIE['tokenn'], 'accept-language: pl-PL'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://upload.allegro.pl/sale/images');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $binary);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    return $result;
}

function getproductimagelist($id)
{
    $product = new SoapClient('https://sklep.satserwis.pl/backend.php/product/soap?wsdl');
    $productimage = new stdClass();
    $productimage->_session_hash = $_COOKIE['sklep'];
    $productimage->product_id = $id;
    $productimage->_offset = 0;
    $productimage->_limit = 20;
    return $product->GetProductImageList($productimage);
}

$imglist = getproductimagelist($id);
$images = array();
foreach ($imglist as $image) {
    file_put_contents('C:/Users/Maciej/Pictures/' . $image->image_filename, base64_decode($image->image));
    $localimage = 'C:/Users/Maciej/Pictures/' . $image->image_filename;
    $imageurl = postImage($localimage);
    $jsonrespons = json_decode($imageurl);
    $photo = new stdClass();
    $photo->url = $jsonrespons->location;
    array_push($images, $photo);
}


$item = new stdClass();
$item->type = "TEXT";
$item->content = $short_description;
$section = new stdClass;
$section->items = array($item);

$item3 = new stdClass();
$item3->type = "IMAGE";
$item3->url = $images[0]->url;
$section3 = new stdClass;
$section3->items = array($item3);

$item2 = new stdClass();
$item2->type = "TEXT";
$item2->content = $description;
$section2 = new stdClass;
$section2->items = array($item2);

$sections = array("sections" => array($section, $section3, $section2));
$desc = json_decode(json_encode($sections));

$maletowary = array(75, 25, 07, 37);
$duzetowary = array(99);

$cennik = "d4aaae68-35f2-4de9-b863-2a7e7f7721b3";

if (in_array(substr($code, 0, 2), $maletowary)) {
    $cennik = "dbd8415c-cd1f-4c76-8e2b-b57f82c231e3";
}
if (in_array(substr($code, 0, 2), $duzetowary)) {
    $cennik = "80364e50-e41d-4440-ab6a-9d9169c91570";
}

$allegro = new AllegroServices();

$res = $allegro->sale("GET", "/products?mode=GTIN&phrase={$man_code}");
$product = $res->products[0];
$parameters = $product->parameters;
array_push($parameters, array("id" => "11323", "valuesIds" => array("11323_1")));
array_push($parameters, array("id" => "225693", "values" => array($man_code), "valuesIds" => array()));

$draft = array(
    "name" => $names,
    "external" => array(
        "id" => $code,
    ),
    "product" => array(
        "id" => $product->id,
    ),
    "category" => array(
        "id" => $product->category->id,
    ),
    "parameters" => $product->parameters,
    "payments" => array(
        "invoice" => "VAT",
    ),
    "images" => $images,
    "description" => $desc,
    "sellingMode" => array(
        "format" => "BUY_NOW",
        "price" => array(
            "amount" => $price,
            "currency" => "PLN",
        ),
    ),
    "tax" => array(
        "rate" => "23.00",
        "subject" => "GOODS",
    ),
    "stock" => array(
        "available" => strval($stock),
        "unit" => "UNIT",
    ),
    "afterSalesServices" => array(
        "impliedWarranty" => array(
            "id" => "77fc4534-9ae2-4d40-a0a5-aafdfd8346fb",
        ),
        "returnPolicy" => array(
            "id" => "6f5e6c13-ce36-4f66-8473-85d1e93cc718",
        ),
        "warranty" => array(
            "id" => "239d4151-c96a-41a7-b2ca-abb68f7aad2b",
        )
    ),
    "location" => array(
        "countryCode" => "PL",
        "province" => "LODZKIE",
        "city" => "Łódź",
        "postCode" => "91-425",
    ),
    "delivery" => array(
        "shippingRates" => array(
            "id" => $cennik,
        ),
        "handlingTime" => "PT24H", //"PT0S" - NATYCHMIAST,
    ),
    "language" => "pl-PL"
);

$i = $allegro->sale("POST", '/offers', $draft);

print_r(json_encode($i));
