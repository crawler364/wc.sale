<?php

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use WC\Sale\Handlers\BasketHandler;

class WCSaleBasket extends CBitrixComponent
{
    /** @var BasketHandler */
    private $basketHandlerClass = BasketHandler::class;

    protected function checkModules(): bool
    {
        if (!Loader::includeModule('wc.core')) {
            throw new LoaderException(Loc::getMessage('WC_BASKET_MODULE_CORE_NOT_INCLUDED'));
        }

        if (!Loader::includeModule('wc.sale')) {
            throw new LoaderException(Loc::getMessage('WC_BASKET_MODULE_CORE_NOT_INCLUDED'));
        }

        return true;
    }

    public function executeComponent()
    {
        $this->checkModules();

        CUtil::InitJSCore(['ajax', 'wc.sale.basket']);

        $basketHandlerClass = $this->arParams['BASKET_HANDLER_CLASS'] ?: $this->basketHandlerClass;
        $basket = $basketHandlerClass::getBasket();

        $this->arResult = $basket->getData();

        $this->includeComponentTemplate();
    }
}
