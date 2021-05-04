<?php

use WC\Sale\Handlers\BasketHandler;

class WCSaleBasket extends CBitrixComponent
{
    /** @var BasketHandler */
    private $basketHandlerClass = BasketHandler::class;

    protected function checkModules(): bool
    {
        if (!\Bitrix\Main\Loader::includeModule('wc.core')) {
            ShowError(\Bitrix\Main\Localization\Loc::getMessage('WC_BASKET_MODULE_CORE_NOT_INCLUDED'));
            return false;
        }

        if (!\Bitrix\Main\Loader::includeModule('wc.sale')) {
            ShowError(\Bitrix\Main\Localization\Loc::getMessage('WC_BASKET_MODULE_SALE_NOT_INCLUDED'));
            return false;
        }

        return true;
    }

    public function executeComponent()
    {
        if ($this->checkModules()) {
            CUtil::InitJSCore(['ajax', 'wc.sale.basket']);

            $basketHandlerClass = $this->arParams['BASKET_HANDLER_CLASS'] ?: $this->basketHandlerClass;
            $basket = $basketHandlerClass::getBasket();

            $this->arResult = $basket->getData();

            $this->includeComponentTemplate();
        }
    }
}
