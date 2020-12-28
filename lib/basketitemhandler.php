<?php


namespace WC\Sale;


use Bitrix\Main\Loader;
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

    public function __construct(BasketItem $basketItem)
    {
        $this->result = new Result();
        $this->mess = new Messages(__FILE__);

        $this->basket = $basketItem->getCollection();
        $this->basketItem = $basketItem;
    }

    public function update()
    {
        // todo добавить сюда проверку количества?

        // Собрать поля для нового basketItem
        if ($this->basketItem->getId() == null) {
            $fields = $this->basketItem->prepareBasketItemFields();
            $this->basketItem->setFields($fields);
        }

        $this->basketItem->setPriceName();

        $this->basketItem->setPropertyArticle();

        $this->result = $this->basket->save();

        if ($this->result->isSuccess()) {
            $basketItemInfo = $this->basketItem->getInfo();
            $this->result->setData($basketItemInfo);
        }

        return $this->result;
    }

    public function delete()
    {
        $this->basketItem->delete();

        $this->result = $this->basket->save();

        return $this->result;
    }

    /**
     * @param $productId
     * @param Basket|null $basket
     * @return BasketItem|null
     */
    public static function getBasketItem($productId, Basket $basket = null): ?BasketItem
    {
        Loader::includeModule('catalog');
        $basket = $basket ?: BasketHandler::getCurrentUserBasket();
        if (\Bitrix\Catalog\ProductTable::getById($productId)->fetch()) {
            return $basket->getItemBy(['PRODUCT_ID' => $productId]) ?: $basket->createItem('catalog', $productId);
        }

        return null;
    }
}