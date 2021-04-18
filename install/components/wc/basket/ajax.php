<?php

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Request;
use Bitrix\Main\Loader;
use WC\Core\Bitrix\Main\Result;
use WC\Sale\Handlers\BasketHandler;

class WCSaleBasketAjaxController extends Controller
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

    public function processAction(string $basketAction, array $product, array $parameters = []): AjaxJson
    {
        $result = new Result();

        $basketHandlerClass = $parameters['BASKET_HANDLER_CLASS'] ?: $this->basketHandlerClass;
        $basket = $basketHandlerClass::getBasket();
        $basketItem = $basket->getItemBy(['PRODUCT_ID' => $product['id']]) ??
            $basketHandlerClass::getBasketItem($product['id'], $basket);

        if (!$basketItem) {
            $result->addError('WC_UNDEFINED_PRODUCT');
        } else {
            $basketHandler = new $basketHandlerClass($basket, $parameters);
            $basketHandler->processBasketItem($basketItem, $basketAction, $product['quantity']);
            $result = $basketHandler->saveBasket();
        }

        return $result->prepareAjaxJson();
    }
}
