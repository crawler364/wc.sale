<?php

use WC\Main\Result;

class WCSaleBasket extends CBitrixComponent implements Bitrix\Main\Engine\Contract\Controllerable
{
    public function configureActions(): array
    {
        return [
            'process' => ['prefilters' => [], 'postfilters' => [],],
        ];
    }

    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }

    public function processAction(){
        $this->result = new Result();

        $product = $this->request->get('product');

        if (!$basketItem = \AF\Sale\BasketHandler::getBasketItem($product['id'])) {
            $this->result->addErrors($this->mess->get('WC_UNDEFINED_PRODUCT'));
        } else {
            $basketHandler = new \AF\Sale\BasketHandler($basketItem);
            $basketHandler->processBasketItem($this->request['act'], $this->request['quantity']);
            $this->result = $basketHandler->saveBasket();
        }
    }
}