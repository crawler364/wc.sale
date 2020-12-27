<?php


namespace WC\Sale;


use Bitrix\Main\Localization\Loc;

class BasketHandler
{
    /**
     * @var BasketItem $basketItem
     * @var Basket $basket
     */
    public $basketItem;
    protected $basket;
    public $quantity;
    protected $productProviderClass = \CCatalogProductProvider::class;
    public $productId;

    /**
     * @param $param = PRODUCT_ID | \Bitrix\Sale\BasketItem
     * @param \Bitrix\Sale\Basket|null $basket
     */
    public function __construct($param, \Bitrix\Sale\Basket $basket = null)
    {
        $this->result = new \WC\Main\Result();
        $this->mess = new \WC\Main\Messages(__FILE__);

        $this->basket = $basket ?: self::getCurrentUserBasket();

        if ($param instanceof \Bitrix\Sale\BasketItem) {
            $this->productId = $param->getProductId();
            $this->basketItem = $param;
        } else {
            $this->productId = $param;
            if (\Bitrix\Catalog\ProductTable::getById($this->productId)->fetch()) {
                $this->basketItem = $this->basket->getItemBy(['PRODUCT_ID' => $this->productId]) ?:
                    $this->basket->createItem('catalog', $this->productId);
            } else {
                $this->result->addError($this->mess->get('WC_INCORRECT_PRODUCT_ID'));
            }
        }
    }

    public function basketItemUpdate()
    {
        if (!$this->result->isSuccess()) {
            return $this->result;
        }

        // Собрать поля для нового basketItem
        if ($this->basketItem->getId() == null) {
            $fields = $this->prepareBasketItemFields();
        }

        $fields['QUANTITY'] = $this->quantity;
        $fields['PRODUCT_PROVIDER_CLASS'] = $this->productProviderClass;

        $this->basketItem->setFields($fields);

        $this->setBasketItemPriceName();

        $this->setBasketItemPropertyArticle();

        $r = $this->basket->save();

        $this->result->mergeResult($r);

        if ($this->result->isSuccess()) {
            $basketItemInfo = $this->basketItem->getInfo();
            $this->result->setData($basketItemInfo);
        }

        return $this->result;
    }

    public function basketItemDelete()
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
     * @param string $math = 'plus' | 'minus'
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function mathBasketItemQuantity(string $math)
    {
        $ratio = \WC\Catalog\Tools::getProductRatio($this->productId);

        if ($this->basketItem) {
            $quantity = $this->basketItem->getQuantity();
        } else {
            $quantity = 0.0;
        }

        switch ($math) {
            case 'plus':
                $quantity += $ratio;
                break;
            case 'minus':
                $quantity -= $ratio;
                break;
        }

        $this->checkBasketItemQuantity($quantity);
    }

    public function checkBasketItemQuantity($quantity = 0.0)
    {
        $ratio = \WC\Catalog\Tools::getProductRatio($this->productId);

        // todo Проверить остатки, установить количество по остаткам
        // $remnants = получить остатки общие\по складам;
        // $quantity = $remnants > $quantity ? $quantity : $remnants;

        // Проверить количество по коэффициенту
        if (is_numeric($quantity) && $quantity > $ratio && $quantity > 0) {
            $multiply = round($quantity / $ratio, 2);
            if (round($multiply, 0) !== $multiply) {
                $multiply = floor($multiply);
                $quantity = $multiply * $ratio;
            }
        } else {
            $quantity = 0.0;
        }

        $this->quantity = $quantity;
    }

    protected function setBasketItemPriceName()
    {
        $notes = unserialize($this->basketItem->getField('NOTES'), ['allowed_classes' => true]);
        $price = \Bitrix\Catalog\GroupTable::getById($this->basketItem->getField('PRICE_TYPE_ID'))->fetch();
        $notes['PRICE_NAME'] = $price['NAME'];

        $this->basketItem->setField('NOTES', serialize($notes));
    }

    public function prepareBasketItemFields()
    {
        // todo универсальный вариант под торговые предложения и товары
        return [];
    }

    protected function setBasketItemPropertyArticle()
    {
        $notes = unserialize($this->basketItem->getField('NOTES'), ['allowed_classes' => true]);
        $this->basketItem->setProperty(Loc::getMessage('WC_SALE_ARTICLE'), 'ARTICLE', $notes['ARTICLE']);
    }

    public static function getIblockElementInfo($productId)
    {
        // todo
        return [];
    }

    public static function getCurrentUserBasket()
    {
        $siteId = \WC\Main\Tools::getSiteId();
        $fUserId = \Bitrix\Sale\Fuser::getId();
        return \Bitrix\Sale\Basket::loadItemsForFUser($fUserId, $siteId);
    }
}