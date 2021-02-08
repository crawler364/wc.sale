<?php

use Bitrix\Main\Loader;

class WCSaleOrderAjaxController extends \Bitrix\Main\Engine\Controller
{
    /** @var \WC\Sale\OrderHandler */
    private $orderHandlerClass = \WC\Sale\OrderHandler::class;

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
        /** @var \WC\Sale\OrderHandler $orderHandler */

        $order = $this->orderHandlerClass::createOrder();
        $orderHandler = new $this->orderHandlerClass($order, $orderData);
        $data = $orderHandler->saveOrder();
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
