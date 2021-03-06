<?php

class WCSaleBasket extends CBitrixComponent
{
    /** @var \WC\Core\Sale\BasketHandler */
    private $basketHandlerClass = \WC\Core\Sale\BasketHandler::class;

    public function executeComponent()
    {
        \CUtil::InitJSCore(['ajax', 'wc.sale.basket']);

        $basketHandlerClass = $this->arParams['BASKET_HANDLER_CLASS'] ?: $this->basketHandlerClass;
        $basket = $basketHandlerClass::getBasket();

        $this->arResult = $basket->getData();

        $this->includeComponentTemplate();
    }
}
