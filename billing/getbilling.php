<pre><?php
        $limit = 100;
        $offset = 0;
        $offset *= $limit;
        $orderid = '78de0337-0c0c-11ed-be81-57847384b812';
        include_once("../allegrofunction.php");
        $allegro = new AllegroServices();

        function cmpw($a, $b)
        {
            return strnatcmp($a->occurredAt, $b->occurredAt);
        }

        $payment = $allegro->billing("GET", '/billing-entries?order.id=' . $orderid);

        print_r($payment);

        $tagi = array('SUC', 'REF', 'HB4', 'PKO', 'HB1', 'HB2');
        foreach ($payment->billingEntries as $pay) {
            if (!in_array($pay->type->id, $tagi)) {
                print_r($pay);
            }
        }

        ?>