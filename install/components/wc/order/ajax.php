<?php


namespace WC\Sale\Components;


use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use WC\Sale\Handlers\OrderHandler;

class OrderAjaxController extends \Bitrix\Main\Engine\Controller
{
    /** @var OrderHandler */
    private $orderHandlerClass = OrderHandler::class;
    private $usePropertiesDefaultValue = false;

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

        $order = $this->orderHandlerClass::createOrder();
        $orderHandler = new $this->orderHandlerClass($order, [
            'ORDER_DATA' => $orderData,
            'USE_PROPERTIES_DEFAULT_VALUE' => $this->usePropertiesDefaultValue,
        ]);
        $result = $orderHandler->saveOrder();

        return $result->prepareAjaxJson();
    }
}
