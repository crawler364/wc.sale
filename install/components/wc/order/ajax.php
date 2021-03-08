<?php

use Bitrix\Main\Context;
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

    public function saveOrderAction(): \Bitrix\Main\Engine\Response\AjaxJson
    {
        /** @var OrderHandler $orderHandler */
        /** @var \Bitrix\Main\Type\ParameterDictionary $files */

        $request = Context::getCurrent()->getRequest();
        $orderData = $request->toArray();

        $properties = $request->getFileList();
        foreach ($properties as $propertyCode => $propertyParams) {
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

        $this->result = new Result();

        $order = $this->orderHandlerClass::createOrder();
        $orderHandler = new $this->orderHandlerClass($order, $orderData);
        $this->result = $orderHandler->saveOrder();

        return $this->result->prepareAjaxJson();
    }
}
