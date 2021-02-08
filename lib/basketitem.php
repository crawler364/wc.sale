<?php


namespace WC\Sale;


use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class BasketItem extends \Bitrix\Sale\BasketItem
{
    public function getInfo(): array
    {
        Loader::includeModule('iblock');

        $productId = $this->getProductId();

        $info['ELEMENT'] = static::getIblockElementInfo($productId);

        $info['PRODUCT_ID'] = (string)$productId;
        $info['NAME'] = (string)$this->getField('NAME');
        $info['WEIGHT'] = (string)$this->getWeight();
        $info['WEIGHT_FORMATTED'] = \WC\Catalog\Tools::formatWeight($info['WEIGHT']);
        $info['QUANTITY'] = (string)$this->getQuantity();
        $info['MEASURE_NAME'] = (string)$this->getField('MEASURE_NAME');
        $info['PRICE'] = (string)$this->getPrice();
        $info['PRICE_FORMATTED'] = \WC\Currency\Tools::format($info['PRICE']);
        $info['PRICE_SUM'] = (string)($info['QUANTITY'] * $info['PRICE']);
        $info['PRICE_SUM_FORMATTED'] = \WC\Currency\Tools::format($info['PRICE_SUM']);
        $info['PRICE_BASE'] = (string)$this->getField('BASE_PRICE');
        $info['PRICE_BASE_FORMATTED'] = \WC\Currency\Tools::format($info['PRICE_BASE']);
        $info['PRICE_BASE_SUM'] = (string)($info['PRICE_BASE'] * $info['QUANTITY']);
        $info['PRICE_BASE_SUM_FORMATTED'] = \WC\Currency\Tools::format($info['PRICE_BASE_SUM']);
        $info['DISCOUNT'] = (string)$this->getDiscountPrice();
        $info['DISCOUNT_PERCENT'] = $this->getField('DISCOUNT_VALUE');
        $info['DISCOUNT_SUM'] = (string)($info['DISCOUNT'] * $info['QUANTITY']);
        $info['DISCOUNT_SUM_FORMATTED'] = \WC\Currency\Tools::format($info['DISCOUNT_SUM']);

        return $info;
    }

    public function setProperty($name, $code, $value)
    {
        $propertyCollection = $this->getPropertyCollection();
        if (!$propertyCollection->getItemByIndex($code)) { // todo исправить, так работать не будет. Настройка свойств для добавления в корзину
            $basketItemProperty = $propertyCollection->createItem();
            $basketItemProperty->setFields([
                'NAME' => $name,
                'CODE' => $code,
                'VALUE' => $value,
            ]);
        }
    }

    public function setPriceName()
    {
        $notes = unserialize($this->getField('NOTES'), ['allowed_classes' => true]);
        $priceTypeId = $this->getField('PRICE_TYPE_ID');
        $price = $priceTypeId ? \Bitrix\Catalog\GroupTable::getById($priceTypeId)->fetch() : null;
        $notes['PRICE_NAME'] = $price['NAME'];

        $this->setField('NOTES', serialize($notes));
    }

    public function setPropertyArticle()
    {
        $notes = unserialize($this->getField('NOTES'), ['allowed_classes' => true]);
        $this->setProperty(Loc::getMessage('WC_SALE_ARTICLE'), 'ARTICLE', $notes['ARTICLE']);
    }

    public function prepareBasketItemFields(): array
    {
        // todo универсальный вариант под торговые предложения и товары
        return [

        ];
    }

    public static function getIblockElementInfo($productId): array
    {
        // todo
        return [];
    }

    /**
     * @param string $action
     * @return float|int
     */
    public function mathQuantity(string $action)
    {
        $ratio = \WC\Catalog\Tools::getProductRatio($this->getProductId());

        $quantity = $this->getQuantity() ?: 0;

        switch ($action) {
            case 'plus':
                $quantity += $ratio;
                break;
            case 'minus':
                $quantity -= $ratio;
                break;
            case 'delete':
            default:
                $quantity = 0;
        }

        return $quantity;
    }

    /**
     * @param float|int $quantity
     * @return float|int
     */
    public function checkQuantity($quantity)
    {
        $ratio = \WC\Catalog\Tools::getProductRatio($this->getProductId());

        // todo Проверить остатки, установить количество по остаткам

        // Проверить количество по коэффициенту
        if (is_numeric($quantity) && $quantity > $ratio && $quantity > 0) {
            $multiply = round($quantity / $ratio, 2);
            if (round($multiply) !== $multiply) {
                $multiply = floor($multiply);
                $quantity = $multiply * $ratio;
            }
        }

        return $quantity;
    }
}
