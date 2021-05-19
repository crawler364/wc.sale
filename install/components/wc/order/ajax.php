<?php


namespace WC\Sale\Components;


use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use WC\Core\Bitrix\Main\Result;
use WC\Sale\Handlers\Order\Handler as OrderHandler;

Loc::loadMessages(__FILE__);

class OrderAjaxController extends \Bitrix\Main\Engine\Controller
{
    /** @var OrderHandler */
    private $cOrderHandler;
    private $arParams;

    public function __construct(\Bitrix\Main\Request $request = null)
    {
        parent::__construct($request);

        $this->checkModules(['wc.core', 'wc.sale']);
    }

    public function configureActions(): array
    {
        return [
            'saveOrder' => [
                'prefilters' => [], 'postfilters' => [],
            ],
        ];
    }

    public function saveOrderAction(): \Bitrix\Main\Engine\Response\AjaxJson
    {
        /** @var OrderHandler $orderHandler */
        /** @var Result $result */

        $this->setArParams($parameters);
        $this->setCOrderHandler();

        $order = $this->cOrderHandler::createOrder();
        $orderHandler = new $this->cOrderHandler($order, [
            'ORDER_DATA' => $this->getOrderData(),
            'USE_PROPERTIES_DEFAULT_VALUE' => false,
        ]);
        $result = $orderHandler->saveOrder();

        return $result->prepareAjaxJson();
    }

    private function checkModules(array $modules): void
    {
        foreach ($modules as $module) {
            if (!Loader::includeModule($module)) {
                throw new LoaderException(Loc::getMessage('WC_ORDER_MODULE_NOT_INCLUDED', ['#REPLACE#' => $module]));
            }
        }
    }

    private function setArParams($parameters): void
    {
        $this->arParams = $parameters;
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

    private function getOrderData(): array
    {
        $orderData = $this->request->toArray();
        $filesProperties = $this->request->getFileList();

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
