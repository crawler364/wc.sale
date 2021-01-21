<?php


class WCSaleOrder extends CBitrixComponent
{
    /** @var \WC\Sale\OrderHandler */
    private $orderHandlerClass = \WC\Sale\OrderHandler::class;

    public function executeComponent()
    {
        \CUtil::InitJSCore(['ajax', 'wc.sale.order']);

        $orderHandlerClass = $this->arParams['ORDER_HANDLER_CLASS'] ?: $this->orderHandlerClass;

        $order = $orderHandlerClass::createOrder();
        $orderHandler = new $orderHandlerClass($order);
        $result = $orderHandler->processOrder();

        // $result = $orderHandler->saveOrder();

        $this->arResult = $result->getData();

        $this->includeComponentTemplate();
    }
}