<?php


namespace WC\Sale\Components;


use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\SystemException;
use WC\Core\Bitrix\Main\Result;
use WC\Sale\Handlers\Order\Handler as OrderHandler;

Loc::loadMessages(__FILE__);

class OrderAjaxController extends \Bitrix\Main\Engine\Controller
{
    private $arParams;
    private $arResult;

    public function __construct(Request $request = null)
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

    protected function prepareParams(): bool
    {
        $this->arParams = $this->getUnsignedParameters();

        return parent::prepareParams();
    }

    protected function processBeforeAction(Action $action): bool
    {
        $this->arResult = $this->getOrderData();

        return parent::processBeforeAction($action);
    }

    public function saveOrderAction(): AjaxJson
    {
        /**
         * @var OrderHandler $cOrderHandler
         * @var OrderHandler $orderHandler
         * @var Result $result
         */

        $cOrderHandler = $this->getCOrderHandler();
        $order = $cOrderHandler::createOrder();
        $orderHandler = new $cOrderHandler($order, [
            'ORDER_DATA' => $this->arResult,
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

    private function getCOrderHandler(): string
    {
        if (class_exists($this->arParams['ORDER_HANDLER_CLASS'])) {
            $cOrderHandler = $this->arParams['ORDER_HANDLER_CLASS'];
        } elseif (class_exists(OrderHandler::class)) {
            $cOrderHandler = OrderHandler::class;
        } else {
            throw new SystemException(Loc::getMessage('WC_ORDER_HANDLER_CLASS_NOT_EXISTS'));
        }

        return $cOrderHandler;
    }

    private function getOrderData(): array
    {
        $orderData = $this->request->toArray(); // todo validate props
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
