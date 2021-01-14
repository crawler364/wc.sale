<?php


namespace WC\Sale;


use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Order;
use WC\Main\Result;

class OrderHandler
{
    public function __construct(Order $order)
    {
        $this->result = new Result();
        Loc::loadMessages(__FILE__);

        $this->order = $order;


        $this->order->setBasket(\WC\Sale\BasketHandler::getCurrentUserBasket());

        $this->order->setPersonTypeId(1);

        foreach ($this->order->getPropertyCollection() as $prop) {

        }

        //$this->result = $this->order->save();
    }

    protected function setOrderPropertys()
    {

    }

    public static function createOrder(): Order
    {
        global $USER;
        $siteId = \WC\Main\Tools::getSiteId();
        $userId = $USER->GetID();
        return Order::create($siteId, $userId);
    }

    public static function getPersonTypes(): ?array
    {
        $res = \Bitrix\Sale\PersonType::getList([
            'order' => ['SORT' => 'ASC'],
        ]);
        while ($ar = $res->fetch()) {
            $personTypes[] = $ar;
        }

        return $personTypes;
    }
}