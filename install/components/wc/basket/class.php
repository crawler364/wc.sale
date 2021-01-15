<?php

use Bitrix\Main\Loader;

class WCSaleBasket extends CBitrixComponent
{
    /** @var \WC\Sale\BasketHandler */
    private $basketHandlerClass = \WC\Sale\BasketHandler::class;

    public function __construct($component = null)
    {
        parent::__construct($component);

        Loader::includeModule('wc.sale');
    }

    public function executeComponent()
    {
        \CUtil::InitJSCore(['ajax', 'wc.sale.basket']);

        $basketHandlerClass = $this->arParams['BASKET_HANDLER_CLASS'] ?: $this->basketHandlerClass;
        $basket = $basketHandlerClass::getCurrentUserBasket();

        $this->arResult = $basket->getInfo();

        $this->includeComponentTemplate();
    }
}