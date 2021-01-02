<?php


namespace WC\Sale;


use Bitrix\Main\Loader;
use WC\Main\Messages;
use WC\Main\Result;

class BasketHandler
{
    /** @var BasketItem */
    protected $basketItem;
    /** @var Basket */
    protected $basket;
    /** @var \CCatalogProductProvider */
    protected $productProvider = \CCatalogProductProvider::class;

    public function __construct($object)
    {
        $this->result = new Result();
        $this->mess = new Messages(__FILE__);

        if ($object instanceof BasketItem) {
            $this->basketItem = $object;
            $this->basket = $object->getCollection();
        } elseif ($object instanceof Basket) {
            $this->basket = $object;
        } else {
            throw new \Bitrix\Main\ArgumentTypeException($object);
        }
    }

    public function processBasketItem($action, $quantity = null): Result
    {
        if ($action != 'set') {
            $quantity = $this->basketItem->mathQuantity($action);
        }

        $this->quantity = $this->basketItem->checkQuantity($quantity);

        if ($this->quantity > 0) {
            if ($this->basketItem->getId() == null) {
                $this->addBasketItemFields();
            } else {
                $this->updateBasketItemFields();
            }
        } else {
            $this->basketItem->delete();
        }

        // todo $this->result += данные по доставке?

        return $this->result;
    }

    public function saveBasket(): Result
    {
        $r = $this->basket->save();

        $this->result->mergeResult($r);

        if ($this->result->isSuccess()) {
            $this->result->setData([
                'ITEM' => $this->basketItem->getInfo(),
                'BASKET' => $this->basket->getInfo(),
            ]);
        }

        return $this->result;
    }

    protected function addBasketItemFields()
    {
        $this->basketItem->setField('PRODUCT_PROVIDER_CLASS', $this->productProvider);

        $this->basketItem->setField('QUANTITY', $this->quantity);

        $fields = $this->basketItem->prepareBasketItemFields();
        $this->basketItem->setFields($fields);

        //$this->basketItem->setPriceName();

        $this->basketItem->setPropertyArticle();
    }

    protected function updateBasketItemFields()
    {
        $this->basketItem->setField('QUANTITY', $this->quantity);
    }

    protected function setProviderBasketItemFields()
    {
        $productProviderFields = $this->productProvider::GetProductData(['PRODUCT_ID' => $this->basketItem->getProductId()]);
        unset($productProviderFields['AVAILABLE_QUANTITY']);
        $this->basketItem->setFields($productProviderFields);
    }

    /**
     * @param $productId
     * @param Basket|null $basket
     * @return BasketItem|null
     */
    public static function getBasketItem($productId, Basket $basket = null): ?BasketItem
    {
        Loader::includeModule('catalog');
        $basket = $basket ?: self::getCurrentUserBasket();
        if (!$basketItem = $basket->getItemBy(['PRODUCT_ID' => $productId])) {
            if (\Bitrix\Catalog\ProductTable::getById($productId)->fetch()) {
                $basketItem = $basket->createItem('catalog', $productId);
            }
        }

        return $basketItem;
    }

    public static function getCurrentUserBasket(): Basket
    {
        $siteId = \WC\Main\Tools::getSiteId();
        $fUserId = \Bitrix\Sale\Fuser::getId();
        return Basket::loadItemsForFUser($fUserId, $siteId);
    }
}