<?php


namespace WC\Sale;


use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Order;
use WC\Main\Result;
use Bitrix\Main\Context;

class OrderHandler
{
    public function __construct(Order $order)
    {
        $this->result = new Result();
        Loc::loadMessages(__FILE__);

        $this->order = $order;
    }

    public function processOrder()
    {
        $context = Context::getCurrent();
        $request = $context->getRequest();

        $personTypes = $this->initPersonType();


        //$a = $order->getPropertyCollection();
        //$properties = $order->loadPropertyCollection();

        $this->order->setBasket(\WC\Sale\BasketHandler::getCurrentUserBasket());


        //$c = $order->getShipmentCollection();
        // $r = $order->getPaymentCollection();

        return $personTypes;
    }

    protected function initPersonType()
    {
        $personTypes = $this->getPersonTypes();
        $personTypeId = 2 ?: $personTypes[0]['ID'];

        $this->order->setPersonTypeId($personTypeId);

        foreach ($personTypes as &$personType) {
            if ($personType['ID'] == $personTypeId) {
                $personType['CHECKED'] = 'Y';
            }
        }

        return $personTypes;
    }

    public function saveOrder()
    {
        return $this->order->save();
    }

    public static function createOrder(): Order
    {
        global $USER;
        $siteId = \WC\Main\Tools::getSiteId();
        $userId = $USER->GetID();
        return Order::create($siteId, $userId);
    }

    protected function getPersonTypes(): ?array
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