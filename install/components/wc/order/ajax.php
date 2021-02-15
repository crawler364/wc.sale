<?php

use Bitrix\Main\Loader;
use WC\Sale\Handlers\OrderHandler;
use WC\Core\Bitrix\Main\Result;

class WCSaleOrderAjaxController extends \Bitrix\Main\Engine\Controller
{
    /** @var OrderHandler */
    private $orderHandlerClass = OrderHandler::class;

    public function __construct(\Bitrix\Main\Request $request = null)
    {
        parent::__construct($request);

        Loader::includeModule('wc.sale');
    }

    public function configureActions(): array
    {
        return [
            'saveOrder' => [
                'prefilters' => [], 'postfilters' => [],
            ],
            'test' => [
                'prefilters' => [], 'postfilters' => [],
            ],
        ];
    }

    public function saveOrderAction($orderData)
    {
        /** @var OrderHandler $orderHandler */
        $this->result = new Result();

        $order = $this->orderHandlerClass::createOrder();
        $orderHandler = new $this->orderHandlerClass($order, $orderData);
        $this->result = $orderHandler->saveOrder();

        return $this->result->prepareAjaxJson();
    }

    public function testAction($formData)
    {
        global $APPLICATION;

        $APPLICATION->IncludeComponent(
            "wc:order",
            ".default",
            [
                "COMPONENT_TEMPLATE" => ".default",
            ],
            false
        );
    }
}
