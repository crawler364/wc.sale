<?php


namespace WC\Sale\Components;


use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Fuser;
use WC\Core\Bitrix\Main\Result;
use WC\Sale\Handlers\Order as OrderHandler;

Loc::loadMessages(__FILE__);

class Order extends \CBitrixComponent
{
    private $orderData;

    public function __construct($component = null)
    {
        parent::__construct($component);

        static::checkModules(['wc.core', 'wc.sale']);
        \CUtil::InitJSCore(['ajax', 'wc.sale.order']);
    }

    public static function checkModules(array $modules): void
    {
        foreach ($modules as $module) {
            if (!Loader::includeModule($module)) {
                throw new LoaderException(Loc::getMessage('WC_ORDER_MODULE_NOT_INCLUDED', ['#REPLACE#' => $module]));
            }
        }
    }

    protected function listKeysSignedParameters(): array
    {
        return [
            'ORDER_HANDLER_CLASS',
        ];
    }

    public function executeComponent()
    {
        global $USER;

        $basket = \WC\Sale\Handlers\Basket::getBasket(Fuser::getId());

        if ($basket->count() > 0) {
            if ($this->arParams['ALLOW_AUTO_REGISTER'] === 'Y' || $USER->IsAuthorized()) {
                $templatePage = 'template';
                $this->setResult();
            } else {
                $templatePage = 'template_auth';
            }
        } else {
            $templatePage = 'template_empty';
        }

        if ($this->request['AJAX'] === 'Y') {
            $this->includeComponentTemplateAjax($templatePage);
        } else {
            $this->includeComponentTemplate($templatePage);
        }
    }

    private function setResult(): void
    {
        /**
         * @var OrderHandler $cOrderHandler
         * @var OrderHandler $orderHandler
         * @var Result $result
         */

        $this->orderData = static::getOrderData();
        $cOrderHandler = $this::getCOrderHandler($this->arParams);
        $order = $cOrderHandler::createOrder();
        $orderHandler = new $cOrderHandler($order, $this->orderData, $this->arParams);
        $result = $orderHandler->processOrder();

        $this->arResult = [
            'DATA' => $result->getData(),
            'ERRORS' => $result->getErrorMessages(),
        ];
    }

    private function includeComponentTemplateAjax($templatePage): void
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();
        $this->includeComponentTemplate($templatePage);
        die;
    }

    public static function getOrderData(): array
    {
        $request = \Bitrix\Main\Context::getCurrent()->getRequest();
        $orderData = $request->toArray(); // todo validate props
        $filesProperties = $request->getFileList();

        foreach ($filesProperties as $propertyCode => $propertyParams) {
            foreach ($propertyParams as $paramName => $propertyValues) {
                if (is_array($propertyValues)) {
                    foreach ($propertyValues as $index => $propertyValue) {
                        $orderData[$propertyCode][$index]['ID'] = '';
                        $orderData[$propertyCode][$index][$paramName] = $propertyValue;
                    }
                } else {
                    $orderData[$propertyCode][$index]['ID'] = '';
                    $orderData[$propertyCode][$index][$paramName] = $propertyValues;
                }
            }
        }

        return $orderData;
    }

    public static function getCOrderHandler($arParams): string
    {
        if (class_exists($arParams['ORDER_HANDLER_CLASS'])) {
            $cOrderHandler = $arParams['ORDER_HANDLER_CLASS'];
        } elseif (class_exists(OrderHandler::class)) {
            $cOrderHandler = OrderHandler::class;
        } else {
            throw new SystemException(Loc::getMessage('WC_ORDER_HANDLER_CLASS_NOT_EXISTS'));
        }

        return $cOrderHandler;
    }
}
