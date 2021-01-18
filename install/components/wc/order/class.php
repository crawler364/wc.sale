<?php

class WCSaleOrder extends CBitrixComponent
{
    /** @var \WC\Sale\OrderHandler */
    private $orderHandlerClass = \WC\Sale\OrderHandler::class;

    public function executeComponent()
    {
        \CUtil::InitJSCore(['ajax', 'wc.sale.order']);

        $order = $this->orderHandlerClass::createOrder();
        $orderHandler = new $this->orderHandlerClass($order);

        $personTypes = $this->orderHandlerClass::getPersonTypes();

        $order->setPersonTypeId(1);
        $a = $order->getPropertyCollection();
        $properties = $order->loadPropertyCollection();
       $c =  $order->getShipmentCollection();

        $this->arResult = [
            'PERSON_TYPES' => $personTypes,
            'PROPERTIES' => $properties->getArray()['properties'],
        ];

        $this->includeComponentTemplate();
    }
}