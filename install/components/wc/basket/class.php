<?php


namespace WC\Sale\Components;


use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Fuser;
use WC\Sale\Handlers\Basket as BasketHandler;

Loc::loadMessages(__FILE__);

class Basket extends \CBitrixComponent
{
    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->checkModules(['wc.core', 'wc.sale']);
        \CUtil::InitJSCore(['ajax', 'wc.sale.basket']);
    }

    protected function listKeysSignedParameters(): array
    {
        return [
            'ORDER_HANDLER_CLASS',
            'PROPERTIES',
        ];
    }

    public function executeComponent()
    {
        /** @var BasketHandler $cBasketHandler */

        $cBasketHandler = $this->getCBasketHandler();

        $basket = $cBasketHandler::getBasket(Fuser::getId());
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

    private function getCBasketHandler(): string
    {
        if (class_exists($this->arParams['BASKET_HANDLER_CLASS'])) {
            $cBasketHandler = $this->arParams['BASKET_HANDLER_CLASS'];
        } elseif (class_exists(BasketHandler::class)) {
            $cBasketHandler = BasketHandler::class;
        } else {
            throw new SystemException(Loc::getMessage('WC_BASKET_HANDLER_CLASS_NOT_EXISTS'));
        }

        return $cBasketHandler;
    }
}
