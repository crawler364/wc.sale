<?php


namespace WC\Sale;


use WC\Main\Messages;
use WC\Main\Result;

class BasketItemHandler
{
    /**
     * @var BasketItem $basketItem
     * @var Basket $basket
     */
    public $basketItem;
    public $basket;
    public $quantity;
    public $productId;

    public function __construct(BasketItem $basketItem)
    {
        $this->result = new Result();
        $this->mess = new Messages(__FILE__);

        $this->basket = $basketItem->getCollection();
        $this->basketItem = $basketItem;
    }

    public function update()
    {
        // Собрать поля для нового basketItem
        if ($this->basketItem->getId() == null) {
            $fields = $this->basketItem->prepareBasketItemFields();
        }

        $this->basketItem->setFields($fields);

        $this->basketItem->setPriceName();

        $this->basketItem->setPropertyArticle();

        $r = $this->basket->save();

        $this->result->mergeResult($r);

        if ($this->result->isSuccess()) {
            $basketItemInfo = $this->basketItem->getInfo();
            $this->result->setData($basketItemInfo);
        }

        return $this->result;
    }

    public function delete()
    {
        if (!$this->result->isSuccess()) {
            return $this->result;
        }

        $this->basketItem->delete();

        $r = $this->basket->save();

        $this->result->mergeResult($r);

        return $this->result;
    }

    /**
     * @param $productId
     * @param Basket|null $basket
     * @return BasketItem|null
     */
    public static function getBasketItem($productId, Basket $basket = null)
    {
        $basket = $basket ?: BasketHandler::getCurrentUserBasket();
        if (\Bitrix\Catalog\ProductTable::getById($productId)->fetch()) {
            return $basket->getItemBy(['PRODUCT_ID' => $productId]) ?: $basket->createItem('catalog', $productId);
        }
        return null;
    }
}