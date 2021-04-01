<?php

use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Request;
use WC\Core\Bitrix\Main\Result;
use Bitrix\Main\Loader;
use WC\Sale\Handlers\BasketHandler;

class WCSaleBasketAjaxController extends \Bitrix\Main\Engine\Controller
{
    /** @var BasketHandler */
    private $basketHandlerClass = BasketHandler::class;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        Loader::includeModule('wc.sale');
    }

    public function configureActions(): array
    {
        return [
            'process' => [
                'prefilters' => [], 'postfilters' => [],
            ],
        ];
    }

    public function processAction(string $basketAction, array $product, $basketHandlerClass = null): AjaxJson
    {
        $result = new Result();

        $basketHandlerClass = $basketHandlerClass ?: $this->basketHandlerClass;

        if (!$basketItem = $basketHandlerClass::getBasketItem($product['id'])) {
            $result->addError('WC_UNDEFINED_PRODUCT');
        } else {
            $basketHandler = new $basketHandlerClass($basketItem);
            $basketHandler->processBasketItem($basketAction, $product['quantity']);
            $result = $basketHandler->saveBasket();
        }

        return $result->prepareAjaxJson();
    }
}
