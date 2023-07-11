<pre>
<?php
include_once("../../../allegrofunction.php");


class Offer
{
    private $data = null;
    private $products = [];

    public function __construct($id)
    {
        $offer = getRequestPublic('https://api.allegro.pl/sale/offers/' . $id);
        $offer = json_decode($offer);
        $this->data = $offer;
        $this->getProducts();
        $this->getProductsFromDB();
    }

    public function getProducts()
    {
        $products = array();
        if (isset($this->data->external->id)) {
            $a = explode(' ', $this->data->external->id);
            for ($i = 0; $i < count($a); $i++) {
                $c = explode('x', $a[$i]);
                $arr['code'] = $c[0];
                $arr['count'] = $c[1] ?? 1;
                $arr['fpp'] = null;
                array_push($products, (object)$arr);
            }
        } else {
            preg_match_all("/\d{4,5}/", substr($this->data->name, -6), $matches);
            $arr['code'] = $matches[0];
            $arr['count'] = 1;
            $arr['fpp'] = null;
            array_push($products, (object)$arr);
        }
        $this->products = $products;
    }

    public function getProductsFromDB()
    {
        $pdo = new PDO('mysql:host=localhost;dbname=satserwis;charset=utf8mb4', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dane = $pdo->prepare('SELECT nazwa,cena,ilosc FROM fpp WHERE kodn = :kod ORDER BY cena DESC');
        for ($i = 0; $i < count($this->products); $i++) {
            $product = $this->products[$i];
            $dane->bindValue(":kod", $product->code);
            $dane->execute();
            $product->fpp = $dane->fetch(PDO::FETCH_OBJ);
        }
    }

    public function __get($name)
    {
        return $this->$name ?? null;
    }
}

$offer = new Offer(10688096143);

print_r($offer->products);
