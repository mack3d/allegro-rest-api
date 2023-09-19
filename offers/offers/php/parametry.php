<pre>
<style>
span{
    padding-right: 10px;
}
</style>
<?php
include_once("../../../allegrofunction.php");

$allegro = new AllegroServices();

if (!isset($_COOKIE['sklep'])) {
    $sesja = new SoapClient('https://sklep.satserwis.pl/backend.php/webapi/soap?wsdl');
    $log = new stdClass();
    $log->username = getenv('SOU');
    $log->password = getenv('SOP');
    setcookie('sklep', $sesja->doLogin($log)->hash, time() + 1800);
}
$up = $allegro->sale("GET", '/offers/unfilled-parameters?limit=1000');

function dodajean($id, $ean)
{
    $ean = array(
        "id" => '225693',
        "valuesIds" => array(),
        "values" => array(
            $ean,
        ),
        "rangeValue" => null,
    );
    $ean = json_decode(json_encode($ean));

    $i = getRequestPublic('https://api.allegro.pl/sale/offers/' . $id);
    $i = json_decode($i);
    $parameters = $i->parameters;
    array_push($parameters, $ean);
    $i->parameters = $parameters;
    $tax = gettaxid($i->category->id);
    $i->tax->percentage = ($tax->percentage != null) ? $tax->percentage : null;
    $i->tax->rate = (isset($tax->rate->id)) ? $tax->rate->id : null;
    $i->tax->subject = (isset($tax->subject->id)) ? $tax->subject->id : null;
    $i->tax->id = $tax->id;

    $i = json_decode(json_encode($i), true);
    $j = putPublic('https://api.allegro.pl/sale/offers/' . $id, $i);

    print_r($j);
}

function getProductData($code)
{
    $product = new SoapClient('https://sklep.satserwis.pl/backend.php/product/soap?wsdl');
    $productcode = new stdClass();
    $productcode->_session_hash = $_COOKIE['sklep'];
    $productcode->code = strval($code);
    try {
        return $product->GetProductByCode($productcode)->man_code;
    } catch (Exception $e) {
        return '';
    }
}

function poka($allegro, $offerid)
{
    $offer = $allegro->sale("/offers/{$offerid->id}");
    /*$ean = '';
    if (isset($offer->external->id)){
        $ean = getProductData($offer->external->id);
    }
    if ($ean != ''){
        dodajean($offer->id,trim($ean));
    }
    print_r($offer);*/
    echo '<li><span>' . $offer->id . '</span><span>' . $offer->name . '</span>';
    echo (isset($offer->external->id)) ? '<span>' . $offer->external->id . '</span>' : '';
    echo '</li>';
}
foreach ($up->offers as $offer) {
    poka($allegro, $offer);
}

?>