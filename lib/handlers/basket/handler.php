<?php


namespace WC\Sale\Handlers\Basket;


use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Handler extends HandlerBase
{
    protected function setBasketItemQuantity(array $product): void
    {
        $quantity = is_numeric($product['QUANTITY']) ? $product['QUANTITY'] : $this->basketItem->mathQuantity($product['ACTION']);
        $this->quantity = $this->basketItem->checkQuantity($quantity);
    }
}
