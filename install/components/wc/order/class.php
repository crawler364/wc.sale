<?php


namespace WC\Sale\Components;


use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use \WC\Sale\Handlers\OrderHandler;

class Order extends \CBitrixComponent
{
    private $orderHandlerClass = OrderHandler::class;

    public function __construct($component = null)
    {
        $this->checkModules();
        $this->orderHandlerClass = $this->arParams['ORDER_HANDLER_CLASS'] ?: $this->orderHandlerClass;
        \CUtil::InitJSCore(['ajax', 'wc.sale.order']);

        parent::__construct($component);
    }

    private function checkModules(): bool
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
        $order = $this->orderHandlerClass::createOrder();
        $orderHandler = new $this->orderHandlerClass($order, [
            'USE_PROPERTIES_DEFAULT_VALUE' => $this->arParams['USE_PROPERTIES_DEFAULT_VALUE'],
        ]);
        $result = $orderHandler->processOrder();

        $this->arResult['DATA'] = $result->getData();
        $this->arResult['ERRORS'] = $result->getErrorMessages();

        $this->includeComponentTemplate();
    }
}
