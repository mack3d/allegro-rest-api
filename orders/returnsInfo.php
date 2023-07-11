<?php

include_once("../allegrofunction.php");

class Returns
{
    public $data = null;
    public $status = null;
    public $datetime = null;
    public $number = null;
    public $carrierId = null;

    public function __construct($customerReturns)
    {
        if ($customerReturns->waybill != '' || $customerReturns->waybill != null) {
            $this->number = $customerReturns->waybill;
            $this->carrierId = $customerReturns->carrierId;
            if ($customerReturns->carrierId == "INPOST") $this->inpostApi();
            if ($customerReturns->carrierId == "ALLEGRO") $this->allegroApi();
        }
    }

    protected function allegroApi()
    {
        $url = getRequestPublic("https://api.allegro.pl/order/carriers/" . $this->carrierId . "/tracking?waybill=" . $this->number);
        $json_data = json_decode($url);
        $waybill = $json_data->waybills[0];
        $this->data = $waybill;
        $this->datetime = date("d.m.Y", strtotime($waybill->trackingDetails->updatedAt));
        $last_status = end($waybill->trackingDetails->statuses);
        $this->status = strtolower($last_status->code);
    }

    protected function inpostApi()
    {
        $url = 'https://api-shipx-pl.easypack24.net/v1/tracking/' . $this->number;
        $headers = ['Content-Type: application/json', 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpc3MiOiJhcGktc2hpcHgtcGwuZWFzeXBhY2syNC5uZXQiLCJzdWIiOiJhcGktc2hpcHgtcGwuZWFzeXBhY2syNC5uZXQiLCJleHAiOjE2MjUwNTA1MDgsImlhdCI6MTYyNTA1MDUwOCwianRpIjoiNjkzZTdlOTQtYzllYi00NGJhLWJiMmYtNzk4YjlhZDkxNDQ5In0.7c0Rakv1jiMowBZo2YnkHPJko58k3ZH2pxMjg04IJdt7uD7LRs-iHkho6TZmtC_4F00KYxxDnw6mBv8tqnVwoQ'];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $json_data = json_decode($result);
        $this->data = $json_data;
        $this->status = strtolower($json_data->status);
        $this->datetime = date("d.m.Y", strtotime($json_data->tracking_details[0]->datetime));
    }
}
