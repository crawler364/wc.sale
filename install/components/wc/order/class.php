<?php


namespace WC\Sale\Components;


use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use WC\Sale\Handlers\Order\Handler as OrderHandler;

Loc::loadMessages(__FILE__);

class Order extends \CBitrixComponent
{
    /** @var OrderHandler $cOrderHandler */
    private $cOrderHandler = OrderHandler::class;

    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->checkModules();
        $this->cOrderHandler = $this->arParams['ORDER_HANDLER_CLASS'] ?: $this->cOrderHandler;
        \CUtil::InitJSCore(['ajax', 'wc.sale.order']);
    }

    public function executeComponent()
    {
        $order = $this->cOrderHandler::createOrder();
        $orderHandler = new $this->cOrderHandler($order, [
            'USE_PROPERTIES_DEFAULT_VALUE' => $this->arParams['USE_PROPERTIES_DEFAULT_VALUE'],
        ]);
        $result = $orderHandler->processOrder();

        $this->arResult['DATA'] = $result->getData();
        $this->arResult['ERRORS'] = $result->getErrorMessages();

        $this->includeComponentTemplate();
    }

    private function checkModules(): bool
    {
        if (!Loader::includeModule('wc.core')) {
            throw new LoaderException(Loc::getMessage('WC_ORDER_MODULE_NOT_INCLUDED', ['#REPLACE#' => 'wc.core']));
        }

        if (!Loader::includeModule('wc.sale')) {
            throw new LoaderException(Loc::getMessage('WC_ORDER_MODULE_NOT_INCLUDED', ['#REPLACE#' => 'wc.sale']));
        }

        return true;
    }
}
