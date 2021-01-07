<?php

use Bitrix\Main\Engine\Response\AjaxJson;
use WC\Main\Result;

class WCSaleBasket extends CBitrixComponent implements Bitrix\Main\Engine\Contract\Controllerable
{
    /** @var \WC\Sale\BasketHandler */
    private $basketHandlerClass = \WC\Sale\BasketHandler::class;

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

        return $this->result->prepareJson();
    }

    public function executeComponent()
    {
        \CUtil::InitJSCore(['ajax']);
        $this->basketHandlerClass = $this->arParams['BASKET_HANDLER_CLASS'] ?: $this->basketHandlerClass;
        $this->basket = $this->basketHandlerClass::getCurrentUserBasket();
        $this->basketItems = $this->basket->getBasketItems();
        $this->arResult = $this->basket->getInfo();

        $this->includeComponentTemplate();
    }
}