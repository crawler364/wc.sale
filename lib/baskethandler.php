<?php


namespace WC\Sale;


class BasketHandler
{
    public function __construct(\Bitrix\Sale\Basket $basket)
    {
        $this->result = new \WC\Main\Result();
        $this->mess = new \WC\Main\Messages(__FILE__);

        $this->basket = $basket;
    }

    /**
     * @return \Bitrix\Sale\Basket
     */
    public static function getCurrentUserBasket()
    {
        $siteId = \WC\Main\Tools::getSiteId();
        $fUserId = \Bitrix\Sale\Fuser::getId();
        return \Bitrix\Sale\Basket::loadItemsForFUser($fUserId, $siteId);
    }
}