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

        static::checkModules(['wc.core', 'wc.sale']);
        \CUtil::InitJSCore(['ajax', 'wc.sale.basket']);
    }

    public function executeComponent()
    {
        /** @var BasketHandler $cBasketHandler */

        $cBasketHandler = static::getCBasketHandler($this->arParams);

        $basket = $cBasketHandler::getBasket(Fuser::getId());
        $this->arResult = $basket->getData();

        $this->includeComponentTemplate();
    }

    public static function checkModules(array $modules): void
    {
        foreach ($modules as $module) {
            if (!Loader::includeModule($module)) {
                throw new LoaderException(Loc::getMessage('WC_BASKET_MODULE_NOT_INCLUDED', ['#REPLACE#' => $module]));
            }
        }
    }

    public static function getCBasketHandler($arParams): string
    {
        if (class_exists($arParams['BASKET_HANDLER_CLASS'])) {
            $cBasketHandler = $arParams['BASKET_HANDLER_CLASS'];
        } elseif (class_exists(BasketHandler::class)) {
            $cBasketHandler = BasketHandler::class;
        } else {
            throw new SystemException(Loc::getMessage('WC_BASKET_HANDLER_CLASS_NOT_EXISTS'));
        }

        return $cBasketHandler;
    }
}
