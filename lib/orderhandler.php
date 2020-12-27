<?php


namespace WC\Sale;


class OrderHandler
{
    public function __construct(\Bitrix\Sale\Order $order = null)
    {
        $this->result = new \WC\Main\Result();
        $this->mess = new \WC\Main\Messages(__FILE__);

        $this->order = $order ?: self::createOrder();
    }

    public static function createOrder(){
        global $USER;
        $siteId = \WC\Main\Tools::getSiteId();
        $userId = $USER->GetID();
        return \Bitrix\Sale\Order::create($siteId, $userId);
    }
}