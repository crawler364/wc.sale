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
        $arRequest = $this->request->toArray();

        $this->arParams = $arRequest['parameters'];

        return true;
    }

    public function saveOrderAction(): AjaxJson
    {
        /**
         * @var OrderHandler $cOrderHandler
         * @var OrderHandler $orderHandler
         * @var  \Bitrix\Main\Result | \Bitrix\Sale\Result $result
         */

        $orderData = $this->class::getOrderData();
        $cOrderHandler = $this->class::getCOrderHandler($this->arParams);
        $order = $cOrderHandler::createOrder();
        $orderHandler = new $cOrderHandler($order, $orderData, $this->arParams);
        $result = $orderHandler->saveOrder();

        if ($result->isSuccess()) {
            $uri = new \Bitrix\Main\Web\Uri('');
            $uri->addParams(['order_id' => $result->getDataField('ORDER_ID')]);
            $redirect = $uri->getUri();
            $result->setData(['redirect' => $redirect]);
        }

        return $result->prepareAjaxJson();
    }
}
