<?php

class WCSaleOrder extends CBitrixComponent implements Bitrix\Main\Engine\Contract\Controllerable
{
    /** @var \WC\Sale\OrderHandler */
    private $orderHandlerClass = \WC\Sale\OrderHandler::class;

    public function configureActions()
    {
        // TODO: Implement configureActions() method.
    }

    public function executeComponent()
    {
        $order = $this->orderHandlerClass::createOrder();
        $orderHandler = new $this->orderHandlerClass($order);

        $personTypes = $this->orderHandlerClass::getPersonTypes();


        $this->arResult = [
            'PERSON_TYPES' => $personTypes,
        ];

        $this->includeComponentTemplate();
    }
}