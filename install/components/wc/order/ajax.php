<?php


namespace WC\Sale\Components;


use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use WC\Core\Bitrix\Main\Result;
use WC\Sale\Handlers\Order as OrderHandler;

Loc::loadMessages(__FILE__);

class OrderAjaxController extends \Bitrix\Main\Engine\Controller
{
    private $arParams;
    private $orderData;
    /** @var Order */
    private $class;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this->class = '\\' . \CBitrixComponent::includeComponentClass('wc:order');
        $this->class::checkModules(['wc.core', 'wc.sale']);
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

        return true;
    }

    protected function processBeforeAction(Action $action): bool
    {
        $this->orderData = $this->class::getOrderData();

        return true;
    }

    public function saveOrderAction(): AjaxJson
    {
        /**
         * @var OrderHandler $cOrderHandler
         * @var OrderHandler $orderHandler
         * @var Result $result
         */

        $cOrderHandler = $this->class::getCOrderHandler($this->arParams);
        $order = $cOrderHandler::createOrder();
        $orderHandler = new $cOrderHandler($order, $this->orderData, $this->arParams);
        $result = $orderHandler->saveOrder();

        return $result->prepareAjaxJson();
    }
}
