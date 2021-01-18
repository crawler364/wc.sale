<?php

use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Request;
use WC\Main\Result;
use Bitrix\Main\Loader;

class WCSaleBasketAjaxController extends \Bitrix\Main\Engine\Controller
{
    /** @var \WC\Sale\BasketHandler */
    private $basketHandlerClass = \WC\Sale\BasketHandler::class;

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
        $this->result = new Result();

        $basketHandlerClass = $basketHandlerClass ?: $this->basketHandlerClass;

        if (!$basketItem = $basketHandlerClass::getBasketItem($product['id'])) {
            $this->result->addError('WC_UNDEFINED_PRODUCT');
        } else {
            $basketHandler = new $basketHandlerClass($basketItem);
            $basketHandler->processBasketItem($basketAction, $product['quantity']);
            $this->result = $basketHandler->saveBasket();
        }

        if ($this->result->isSuccess()) {
            $basket = $basketHandlerClass::getCurrentUserBasket();
            $this->result->setData([
                'BASKET_ITEM' => $basketItem->getInfo(),
                'BASKET' => $basket->getInfo(),
            ]);
        }

        return $this->result->prepareAjaxJson();
    }
}