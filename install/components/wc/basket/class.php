<?php


namespace WC\Sale\Components;


use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Fuser;
use WC\Sale\Handlers\Basket\Handler as BasketHandler;

Loc::loadMessages(__FILE__);

class Basket extends \CBitrixComponent
{
    /** @var BasketHandler $cBasketHandler */
    private $cBasketHandler = BasketHandler::class;

    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->checkModules();
        $this->cBasketHandler = $this->arParams['CLASS_BASKET_HANDLER'] ?: $this->cBasketHandler;
        \CUtil::InitJSCore(['ajax', 'wc.sale.basket']);
    }

    public function executeComponent()
    {
        $basket = $this->cBasketHandler::getBasket(Fuser::getId());
        $this->arResult = $basket->getData();

        $this->includeComponentTemplate();
    }

    private function checkModules(): bool
    {
        if (!Loader::includeModule('wc.core')) {
            throw new LoaderException(Loc::getMessage('WC_BASKET_MODULE_NOT_INCLUDED', ['#REPLACE#' => 'wc.core']));
        }

        if (!Loader::includeModule('wc.sale')) {
            throw new LoaderException(Loc::getMessage('WC_BASKET_MODULE_NOT_INCLUDED', ['#REPLACE#' => 'wc.sale']));
        }

        return true;
    }
}
