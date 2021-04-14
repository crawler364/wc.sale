<?php

use WC\Sale\Handlers\BasketHandler;

class WCSaleBasket extends CBitrixComponent
{
    /** @var BasketHandler */
    private $basketHandlerClass = BasketHandler::class;

    public function executeComponent()
    {
        CUtil::InitJSCore(['ajax', 'wc.sale.basket']);

        $basketHandlerClass = $this->arParams['BASKET_HANDLER_CLASS'] ?: $this->basketHandlerClass;
        $basket = $basketHandlerClass::getBasket();

        $this->arResult = $basket->getData();

        $this->includeComponentTemplate();
    }
}
