<?php


namespace WC\Sale\Components;


use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use WC\Sale\Handlers\Order\Handler as OrderHandler;

Loc::loadMessages(__FILE__);

class Order extends \CBitrixComponent
{
    /** @var OrderHandler $cOrderHandler */
    private $cOrderHandler;

    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->checkModules(['wc.core', 'wc.sale']);
        \CUtil::InitJSCore(['ajax', 'wc.sale.order']);
    }

    public function executeComponent()
    {
        $this->setCOrderHandler();

        $order = $this->cOrderHandler::createOrder();
        $orderHandler = new $this->cOrderHandler($order, [
            'USE_PROPERTIES_DEFAULT_VALUE' => $this->arParams['USE_PROPERTIES_DEFAULT_VALUE'],
        ]);
        $result = $orderHandler->processOrder();

        $this->arResult['DATA'] = $result->getData();
        $this->arResult['ERRORS'] = $result->getErrorMessages();

        if ($this->request['AJAX_MODE'] == 'Y') {
            $this->includeComponentTemplateAjax();
        } else {
            $this->includeComponentTemplate();
        }
    }

    private function checkModules(array $modules): void
    {
        foreach ($modules as $module) {
            if (!Loader::includeModule($module)) {
                throw new LoaderException(Loc::getMessage('WC_ORDER_MODULE_NOT_INCLUDED', ['#REPLACE#' => $module]));
            }
        }
    }

    private function setCOrderHandler(): void
    {
        if (class_exists($this->arParams['ORDER_HANDLER_CLASS'])) {
            $this->cOrderHandler = $this->arParams['ORDER_HANDLER_CLASS'];
        } elseif (class_exists(OrderHandler::class)) {
            $this->cOrderHandler = OrderHandler::class;
        } else {
            throw new SystemException(Loc::getMessage('WC_ORDER_HANDLER_CLASS_NOT_EXISTS'));
        }
    }

    private function includeComponentTemplateAjax(): void
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();
        $this->includeComponentTemplate();
        die;
    }
}
