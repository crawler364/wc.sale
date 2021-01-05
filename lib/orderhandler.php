<?php


namespace WC\Sale;


use Bitrix\Main\Localization\Loc;
use WC\Main\Result;

class OrderHandler
{
    public function __construct(\Bitrix\Sale\Order $order = null)
    {
        $this->result = new Result();
        Loc::loadMessages(__FILE__);

        $this->order = $order ?: self::createOrder();
    }

    public static function createOrder(){
        global $USER;
        $siteId = \WC\Main\Tools::getSiteId();
        $userId = $USER->GetID();
        return \Bitrix\Sale\Order::create($siteId, $userId);
    }
}