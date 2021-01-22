<?php


namespace WC\Sale;


class Order extends \Bitrix\Sale\Order
{
    public function getInfo(): array
    {
        $basket = $this->getBasket();
        $basketInfo = $basket->getInfo();

        return $basketInfo['INFO'];
    }
}