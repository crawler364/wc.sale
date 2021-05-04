<?php

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use \WC\Sale\Handlers\OrderHandler;

class WCSaleOrder extends CBitrixComponent
{
    /** @var OrderHandler */
    private $orderHandlerClass = OrderHandler::class;

    protected function checkModules(): bool
    {
        if (!Loader::includeModule('wc.core')) {
            throw new LoaderException(Loc::getMessage('WC_ORDER_MODULE_CORE_NOT_INCLUDED'));
        }

        if (!Loader::includeModule('wc.sale')) {
            throw new LoaderException(Loc::getMessage('WC_ORDER_MODULE_SALE_NOT_INCLUDED'));
        }

        return true;
    }

    public function executeComponent()
    {
        $this->checkModules();

        \CUtil::InitJSCore(['ajax', 'wc.sale.order']);

        $orderHandlerClass = $this->arParams['ORDER_HANDLER_CLASS'] ?: $this->orderHandlerClass;

        $order = $orderHandlerClass::createOrder();
        $orderHandler = new $orderHandlerClass($order);

        if (isset($this->arParams['PROPERTIES_DEFAULT_VALUE'])) {
            $orderHandler->propertiesDefaultValue = $this->arParams['PROPERTIES_DEFAULT_VALUE'];
        }

        $result = $orderHandler->processOrder();

        $this->arResult['DATA'] = $result->getData();
        $this->arResult['ERRORS'] = $result->getErrorMessages();

        $this->includeComponentTemplate();
    }
}
