<?php

include_once("database.class.php");

class orders extends db
{
	protected int $readyCountOrders = 0;
	protected int $totalCountOrders = 0;
	private int $offset = 0;

	public function __construct()
	{
		parent::__construct();
		$stmt = $this->con->query('SELECT COUNT(IF(statusfod = "READY_FOR_PROCESSING", 1, null)) as ready, COUNT(fod) as total FROM newallegroorders');
		$result = $stmt->fetch();
		$this->readyCountOrders = $result->ready;
		$this->totalCountOrders = $result->total;
	}

	public function __get($props)
    {
        if(property_exists(__CLASS__, $props)){
            return $this->{$props}; 
        }else{
			return null;
		}
	}

	public function getOrders($limit = 30, $page = 0)
    {   
		$this->__construct();

		if ($limit < $this->readyCountOrders){		
			if ($page === 0)
			{
				$limit = $this->readyCountOrders;
			}
			
			if ($page === 1)
			{
				$this->offset = $this->readyCountOrders;
			}
			
			if ($page > 1)
			{
				$this->offset = $this->readyCountOrders + (($page-1) * $limit);
			}
		}else{
			$this->offset = $page * $limit;
		}

        $stmt = $this->con->prepare('SELECT * FROM newallegroorders ORDER BY CASE statusfod WHEN "READY_FOR_PROCESSING" THEN 0 ELSE 1 END, readytime DESC LIMIT :offset, :limit');
        $stmt->bindValue(":offset", $this->offset, PDO::PARAM_INT);
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

	public function findOrdersByIds($ids)
    {   
        $stmt = $this->con->prepare('SELECT * FROM newallegroorders WHERE FIND_IN_SET(fod,:ids) ORDER BY readytime DESC');
		$stmt->bindValue(":ids", $ids, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }

	public function getOrderById($id)
    {   
        $stmt = $this->con->prepare('SELECT * FROM newallegroorders WHERE fod = :fod');
        $stmt->bindValue("fod", $id, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }
}


class addOrder extends db
{
	public $id;
	public $messageToSeller;
	public $buyer;
	public $payment;
	public $status;
	public $fulfillment;
	public $delivery;
	public $invoice;
	public $lineItems;
	public $surcharges;
	public $discounts;
	public $summary;
	public $updatedAt;
	public $revision;
	public $isExist = false;

	public function __construct($orderDetails){
		parent::__construct();
		$this->id = $orderDetails->id;
		$this->$messageToSeller = $orderDetails->messageToSeller;
		$this->$buyer = $orderDetails->buyer;
		$this->$payment = $orderDetails->buyer;
		$this->$status = $orderDetails->status;
		$this->$fulfillment = $orderDetails->fulfillment;
		$this->$delivery = $orderDetails->delivery;
		$this->$invoice = $orderDetails->invoice;
		$this->$lineItems = $orderDetails->lineItems;
		$this->$surcharges = $orderDetails->surcharges;
		$this->$discounts = $orderDetails->discounts;
		$this->$summary = $orderDetails->summary;
		$this->$updatedAt = $orderDetails->updatedAt;
		$this->$revision = $orderDetails->revision;
	}

	private function addMessageToSeller(){
		if ($this->messageToSeller != null && trim($this->messageToSeller) != ""){
			$stmt = $this->con->prepare('INSERT INTO newallegromessage (fod, messagetoseller) VALUES (:fod,:messagetoseller)');
			$stmt->bindValue(':fod', $this->id, PDO::PARAM_STR);
			$stmt->bindValue(':messagetoseller', trim($this->messageToSeller), PDO::PARAM_STR);
			$stmt->execute();
		}
	}
}

