<?php
include_once("../../../allegrofunction.php");

$allegro = new AllegroServices();

$data = json_decode(file_get_contents('php://input'), true);

$offset = $data['offset'];
$status = $data['status'];
$sortby = $data['sort'];
$limit = $data['limit'];
$cenaod = trim($data['cenaod']);
$cenado = trim($data['cenado']);
$codes = $data['codes'];
$utrzymaniowa = $data['utrzymaniowa'];
$offers_id = (!empty($data['offerids'])) ? "offer.id=" . implode("&offer.id=", array_unique($data['offerids'])) : $data['offerids'];
$name = (!empty($data['name'])) ? strtolower("&name=" . implode("&name=", array_unique($data['name']))) : '';

$offset = $offset * $limit;
$status = ($status != "ALL") ? '&publication.status=' . $status : '';
$sort = ($sortby != "default" && $sortby != "-default" && $sortby != "events" && $sortby != "sale.time") ? '&sort=' . $sortby : '';
$limit = ($limit == '') ? 1 : $limit;
$cenaod = ($cenaod == '') ? '' : "&sellingMode.price.amount.gte=" . $cenaod;
$cenado = ($cenado == '') ? '' : "&sellingMode.price.amount.lte=" . $cenado;
$response = array();

function codesFromOffer($offer)
{
    if (isset($offer->external->id)) {
        preg_match_all("/\d{4,5}/", $offer->external->id, $matches);
    } else {
        preg_match_all("/\d{4,5}/", substr($offer->name, -6), $matches);
    }
    return $matches[0];
}

function allOriginalCodesFromOffers($offers)
{
    $allcodes = array();
    foreach ($offers as $offer) {
        $codes = codesFromOffer($offer);
        $allcodes = array_merge($allcodes, $codes);
    }
    return array_unique($allcodes);
}

function allCodesFromOffers($offers)
{
    $allcodes = array();
    foreach ($offers as $offer) {
        $codes = codesFromOffer($offer);
        foreach ($codes as $code) {
            if (strlen($code) > 4) array_push($codes, substr($code, 0, 4));
        }
        $allcodes = array_merge($allcodes, $codes);
    }
    return array_unique($allcodes);
}

function getOffers($allegro, $limit, $offset, $sort, $status, $cenado, $cenaod, $offers_id, $name)
{
    $params = 'limit=' . $limit . '&offset=' . $offset . $sort . $status . $cenado . $cenaod . $name;
    $params = (!empty($offers_id)) ? $offers_id : $params;
    $offers = $allegro->sale('GET', '/offers?' . $params);
    return (array)$offers;
}

function lastSaleOffers($allegro, $limit, $offset, $offers_id)
{
    $params = 'limit=' . $limit . '&offset=' . $offset . '&fulfillment.status=PROCESSING';
    $offers = $allegro->order('GET', '/checkout-forms?' . $params);
    $orders = json_decode($offers);
    $items_id = array();
    foreach ($orders->checkoutForms as $order) {
        foreach ($order->lineItems as $item) {
            array_push($items_id, $item->offer->id);
        }
    }
    return (!empty($items_id)) ? "offer.id=" . implode("&offer.id=", array_unique($items_id)) : $items_id;
}

function getOffersOfCodes($codes)
{
    $res = array("offers" => array(), "count" => 0, "totalCount" => 0);
    $offersFind = array();
    $offers = getalloffers("ALL");
    foreach ($offers as $offer) {
        $extcodes = codesFromOffer($offer);
        if (!empty(array_intersect($codes, $extcodes))) array_push($offersFind, $offer);
    }
    $res['offers'] = $offersFind;
    $res['count'] = count($offersFind);
    $res['totalCount'] = count($offersFind);
    return $res;
}

if ($sortby == "sale.time") {
    $offers_id = lastSaleOffers($allegro, $limit, $offset, $sort, $status, $cenado, $cenaod, $offers_id, $name);
}
if (!empty($codes)) {
    $response = getOffersOfCodes($codes);
} else {
    $response = getOffers($allegro, $limit, $offset, $sort, $status, $cenado, $cenaod, $offers_id, $name);
    if ($utrzymaniowa > 0) {
        $original_codes = allOriginalCodesFromOffers($response['offers']);
        $response = getOffersOfCodes($original_codes);
    }
}
$response['allcodes'] = allCodesFromOffers($response['offers']);

echo json_encode($response);
