<?php


namespace WC\Sale;


class BasketItemHandler
{
    /**
     * @param $productId
     * @param \Bitrix\Sale\Basket|null $basket
     * @return \Bitrix\Sale\BasketItem
     */
    public static function getBasketItem($productId, \Bitrix\Sale\Basket $basket = null)
    {
        $basket = $basket ?: \WC\Sale\BasketHandler::getCurrentUserBasket();
        if (\Bitrix\Catalog\ProductTable::getById($productId)->fetch()) {
            return $basket->getItemBy(['PRODUCT_ID' => $productId]) ?: $basket->createItem('catalog', $productId);
        }
        return null;
    }
}