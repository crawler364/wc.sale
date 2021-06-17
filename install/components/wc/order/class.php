<?php


namespace WC\Sale\Components;


use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Fuser;
use WC\Core\Bitrix\Main\Result;
use WC\Sale\Handlers\Order as OrderHandler;
use \WC\Sale\Handlers\Basket;

Loc::loadMessages(__FILE__);

class Order extends \CBitrixComponent
{
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

    public function executeComponent()
    {
        /**
         * @var OrderHandler $cOrderHandler
         * @var OrderHandler $orderHandler
         * @var Result $result
         */

        global $USER;
        $isAuthorized = $USER->IsAuthorized();
        $basket = Basket::getBasket(Fuser::getId());
        $cOrderHandler = $this::getCOrderHandler($this->arParams);

        if ($this->request['order_id'] && $isAuthorized) {
            $templatePage = 'template_accepted';
            $result = $cOrderHandler::loadOrder(['ID' => $this->request['order_id'], 'USER_ID' => $USER->GetID()]);

            if ($result->isSuccess()) {
                $orderHandler = new $cOrderHandler($result->getDataField('ORDER'));
                $result = $orderHandler->getOrder();
            }
        } elseif ($basket->count() > 0) {
            if ($this->arParams['ALLOW_AUTO_REGISTER'] === 'Y' || $isAuthorized) {
                $templatePage = 'template';
                $orderData = static::getOrderData();
                $order = $cOrderHandler::createOrder();

                $orderHandler = new $cOrderHandler($order, $orderData, $this->arParams);
                $result = $orderHandler->refreshOrder();
            } else {
                $templatePage = 'template_auth';
            }
        } else {
            $templatePage = 'template_empty';
        }

        if ($result instanceof Result) {
            $this->arResult = [
                'DATA' => $result->getData(),
                'ERRORS' => $result->getErrorMessages(),
            ];
        }

        if ($this->request['AJAX_CALL'] === 'Y') {
            $this->includeComponentTemplateAjax($templatePage);
        } else {
            $this->includeComponentTemplate($templatePage);
        }
    }

    private function includeComponentTemplateAjax($templatePage): void
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();
        $this->includeComponentTemplate($templatePage);
        die;
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
}
