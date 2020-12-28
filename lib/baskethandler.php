<?php


namespace WC\Sale;


use WC\Main\Messages;
use WC\Main\Result;

class BasketHandler
{
    /**
     * @var Basket $basket
     */
    public $basket;

    public function __construct(Basket $basket)
    {
        $this->result = new Result();
        $this->mess = new Messages(__FILE__);

        $this->basket = $basket;
    }

    public static function getCurrentUserBasket(): Basket
    {
        $siteId = \WC\Main\Tools::getSiteId();
        $fUserId = \Bitrix\Sale\Fuser::getId();
        return Basket::loadItemsForFUser($fUserId, $siteId);
    }
}