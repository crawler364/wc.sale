<?php

use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Request;
use WC\Core\Bitrix\Main\Result;
use Bitrix\Main\Loader;

class WCSaleBasketAjaxController extends \Bitrix\Main\Engine\Controller
{
    /** @var \WC\Core\Sale\BasketHandler */
    private $basketHandlerClass = \WC\Core\Sale\BasketHandler::class;

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
            $basket = $basketHandlerClass::getBasket();
            $this->result->setData([
                'BASKET_ITEM' => $basketItem->getInfo(),
                'BASKET' => $basket->getData(),
            ]);
        }

        return $this->result->prepareAjaxJson();
    }
}
