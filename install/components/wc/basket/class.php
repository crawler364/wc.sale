<?php

use WC\Main\Result;
use WC\Main\Localization\Loc;

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

    public function processAction($basketAction, $product, $basketHandlerClass): Result
    {
        $this->result = new Result();

        if (!$basketItem = $basketHandlerClass::getBasketItem($product['id'])) {
            $this->result->addErrors(Loc::getMessageExt('WC_UNDEFINED_PRODUCT'));
        } else {
            $basketHandler = new $basketHandlerClass($basketItem);
            $basketHandler->processBasketItem($basketAction, $product['quantity']);
            $this->result = $basketHandler->saveBasket();
        }

        return $this->result;
    }

    public function executeComponent()
    {
        $this->basketHandlerClass = $this->arParams['BASKET_HANDLER_CLASS'] ?: $this->basketHandlerClass;
        $this->basket = $this->basketHandlerClass::getCurrentUserBasket();
        $this->basketItems = $this->basket->getBasketItems();
        $this->arResult = $this->basket->getInfo();

        $this->includeComponentTemplate();
    }
}