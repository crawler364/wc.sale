<?php


namespace WC\Sale;


class BasketHandler
{
    public function __construct(Basket $basket)
    {
        $this->result = new \WC\Main\Result();
        $this->mess = new \WC\Main\Messages(__FILE__);

        $this->basket = $basket;
    }

    public static function getCurrentUserBasket(): Basket
    {
        $siteId = \WC\Main\Tools::getSiteId();
        $fUserId = \Bitrix\Sale\Fuser::getId();
        return Basket::loadItemsForFUser($fUserId, $siteId);
    }
}