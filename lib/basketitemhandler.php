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
    public $productProviderClass = \CCatalogProductProvider::class;

    public function __construct(BasketItem $basketItem)
    {
        $this->result = new Result();
        $this->mess = new Messages(__FILE__);

        $this->basket = $basketItem->getCollection();
        $this->basketItem = $basketItem;
        $this->basketItem->setField('PRODUCT_PROVIDER_CLASS', $this->productProviderClass);
    }

    public function process($action, $quantity = null)
    {
        /** @var \Bitrix\Main\Result $r */

        if ($action != 'set') {
            $quantity = $this->basketItem->mathQuantity($action);
        }

        $this->quantity = $this->basketItem->checkQuantity($quantity);

        if ($this->quantity > 0) {
            if ($this->basketItem->getId() == null) {
                $this->add();
            } else {
                $this->update();
            }
        } else {
            $this->delete();
        }

        $r = $this->basket->save();
        $this->result->mergeResult($r);

        if ($this->result->isSuccess()) {
            $this->result->setData([
                'ITEM' => $this->basketItem->getInfo(),
                'BASKET' => $this->basket->getInfo(),
            ]);
        }

        // todo $this->result += данные по доставке?

        return $this->result;
    }

    protected function add()
    {
        $this->basketItem->setField('QUANTITY', $this->quantity);

        $fields = $this->basketItem->prepareBasketItemFields();
        $this->basketItem->setFields($fields);

        $this->basketItem->setPriceName();

        $this->basketItem->setPropertyArticle();
    }

    protected function update()
    {
        /** @var \CCatalogProductProvider $productProvider */
        /*$this->productProvider = $this->basketItem->getProvider();
        $productProviderFields = $this->productProviderClass::GetProductData(['PRODUCT_ID' => $this->basketItem->getProductId()]);
        $this->basketItem->setFields($productProviderFields);*/

        $this->basketItem->setField('QUANTITY', $this->quantity);
    }

    protected function delete()
    {
        $this->basketItem->delete();
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
        if (!$basketItem = $basket->getItemBy(['PRODUCT_ID' => $productId])) {
            if (\Bitrix\Catalog\ProductTable::getById($productId)->fetch()) {
                $basketItem = $basket->createItem('catalog', $productId);
            }
        }

        return $basketItem;
    }
}