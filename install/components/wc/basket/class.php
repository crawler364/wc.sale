<?php


namespace WC\Sale\Components;


use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use WC\Sale\Handlers\BasketHandler;

class Basket extends \CBitrixComponent
{
    private $basketHandlerClass = BasketHandler::class;

    public function __construct($component = null)
    {
        $this->checkModules();
        $this->basketHandlerClass = $this->arParams['BASKET_HANDLER_CLASS'] ?: $this->basketHandlerClass;
        \CUtil::InitJSCore(['ajax', 'wc.sale.basket']);

        parent::__construct($component);
    }

    private function checkModules(): bool
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
        $basket = $this->basketHandlerClass::getBasket();
        $this->arResult = $basket->getData();

        $this->includeComponentTemplate();
    }
}
