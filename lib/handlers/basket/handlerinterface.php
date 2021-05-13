<?php


namespace WC\Sale\Handlers\Basket;


use WC\Core\Bitrix\Main\Result;
use WC\Sale\BasketItem;
use WC\Sale\Basket;

interface HandlerInterface
{
    /**
     * @param int $fUserId
     * @return Basket|\Bitrix\Sale\BasketBase
     */
    public static function getBasket(int $fUserId);

    /**
     * @param int $productId
     * @param Basket|\Bitrix\Sale\BasketBase $basket
     * @return BasketItem|\Bitrix\Sale\BasketItemBase
     */
    public static function createBasketItem(int $productId, Basket $basket);

    /**
     * @param BasketItem $basketItem
     * @param array $product
     * @return void
     */
    public function processBasketItem(BasketItem $basketItem, array $product): void;

    /**
     * @return Result
     */
    public function saveBasket(): Result;
}
