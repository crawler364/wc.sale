<?php

use Bitrix\Main\Loader;

class WCSaleOrder extends CBitrixComponent
{
    /** @var \WC\Sale\OrderHandler */
    private $orderHandlerClass = \WC\Sale\OrderHandler::class;

    public function __construct($component = null)
    {
        parent::__construct($component);

        Loader::includeModule('wc.sale');
    }

    public function executeComponent()
    {
        \CUtil::InitJSCore(['ajax', 'wc.sale.order']);

        $order = $this->orderHandlerClass::createOrder();
        $orderHandler = new $this->orderHandlerClass($order);

        $personTypes = $this->orderHandlerClass::getPersonTypes();

        // $a = $order->getPropertyCollection();
        $properties = $order->loadPropertyCollection();

        $this->arResult = [
            'PERSON_TYPES' => $personTypes,
            'PROPERTIES' => $properties->getArray()['properties'],
        ];

        $this->includeComponentTemplate();
    }
}