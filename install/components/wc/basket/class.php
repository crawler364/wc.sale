<?php

use WC\Main\Result;
use WC\Main\Localization\Loc;

class WCSaleBasket extends CBitrixComponent implements Bitrix\Main\Engine\Contract\Controllerable
{
    /** @var \WC\Sale\BasketHandler */
    private $basketHandler = \WC\Sale\BasketHandler::class;

    public function configureActions(): array
    {
        return [
            'process' => [
                'prefilters' => [], 'postfilters' => [],
            ],
        ];
    }

    public function processAction()
    {
        $this->result = new Result();

        $product = $this->request->get('product');

        if (!$basketItem = \AF\Sale\BasketHandler::getBasketItem($product['id'])) {
            $this->result->addErrors(Loc::getMessageExt('WC_UNDEFINED_PRODUCT'));
        } else {
            $basketHandler = new \AF\Sale\BasketHandler($basketItem);
            $basketHandler->processBasketItem($this->request['act'], $this->request['quantity']);
            $this->result = $basketHandler->saveBasket();
        }
    }

    private function setBasketHandler()
    {
        $this->basketHandler = $this->arParams['BASKET_HANDLER'] ?: $this->basketHandler;
    }

    public function executeComponent()
    {
        $this->setBasketHandler();
        $this->basket = $this->basketHandler::getCurrentUserBasket();
        $this->basketItems = $this->basket->getBasketItems();
        $this->arResult = $this->basket->getInfo();

        $this->includeComponentTemplate();
    }
}