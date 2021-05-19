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
    private $cBasketHandler;

    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->checkModules(['wc.core', 'wc.sale']);
        \CUtil::InitJSCore(['ajax', 'wc.sale.basket']);
    }

    public function executeComponent()
    {
        $this->setCBasketHandler();

        $basket = $this->cBasketHandler::getBasket(Fuser::getId());
        $this->arResult = $basket->getData();

        $this->includeComponentTemplate();
    }

    private function checkModules(array $modules): void
    {
        foreach ($modules as $module) {
            if (!Loader::includeModule($module)) {
                throw new LoaderException(Loc::getMessage('WC_BASKET_MODULE_NOT_INCLUDED', ['#REPLACE#' => $module]));
            }
        }
    }

    private function setCBasketHandler(): void
    {
        if (class_exists($this->arParams['BASKET_HANDLER_CLASS'])) {
            $this->cBasketHandler = $this->arParams['BASKET_HANDLER_CLASS'];
        } elseif (class_exists(BasketHandler::class)) {
            $this->cBasketHandler = BasketHandler::class;
        } else {
            throw new SystemException(Loc::getMessage('WC_BASKET_HANDLER_CLASS_NOT_EXISTS'));
        }
    }
}
