<?php

use \WC\Sale\Handlers\OrderHandler;

class WCSaleOrder extends CBitrixComponent
{
    /** @var OrderHandler */
    private $orderHandlerClass = OrderHandler::class;

    public function executeComponent()
    {
        \CUtil::InitJSCore(['ajax', 'wc.sale.order']);

        $orderHandlerClass = $this->arParams['ORDER_HANDLER_CLASS'] ?: $this->orderHandlerClass;

        $order = $orderHandlerClass::createOrder();
        $orderHandler = new $orderHandlerClass($order);

        if (isset($this->arParams['PROPERTIES_DEFAULT_VALUE'])) {
            $orderHandler->propertiesDefaultValue = $this->arParams['PROPERTIES_DEFAULT_VALUE'];
        }

        $result = $orderHandler->processOrder();

        $this->arResult['DATA'] = $result->getData();
        $this->arResult['ERRORS'] = $result->getErrorMessages();

        $this->includeComponentTemplate();
    }
}
