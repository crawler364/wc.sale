<?php


namespace WC\Sale;


use Bitrix\Main\Localization\Loc;

class BasketItem extends \Bitrix\Sale\BasketItem
{
    public $basketHandler = basketHandler::class;

    public function getInfo()
    {
        \Bitrix\Main\Loader::includeModule('iblock');

        $productId = $this->getProductId();

        $info['ELEMENT'] = static::getIblockElementInfo($productId);

        $info['MAIN_SECTION'] = \Bitrix\Iblock\SectionTable::getList([
            'filter' => ['=ID' => $info['ELEMENT']['IBLOCK_SECTION_ID']],
            'select' => ['ID', 'CODE', 'NAME'],
        ])->fetch();

        $info['ID'] = (string)$productId;
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
        $info['DISCOUNT'] = (string)$this->getField('DISCOUNT_PRICE');
        $info['DISCOUNT_PERCENT'] = $this->getField('DISCOUNT_VALUE');
        $info['DISCOUNT_SUM'] = (string)($info['DISCOUNT'] * $info['QUANTITY']);
        $info['DISCOUNT_SUM_FORMATTED'] = \WC\Currency\Tools::format($info['DISCOUNT_SUM']);

        return $info;
    }

    public function setProperty($name, $code, $value)
    {
        $propertyCollection = $this->getPropertyCollection();
        if (!$propertyCollection->getItemByIndex($code)) {
            $basketItemProperty = $propertyCollection->createItem();
            $basketItemProperty->setFields([
                'NAME' => $name,
                'CODE' => $code,
                'VALUE' => $value,
            ]);
        }
    }

    public function setBasketItemPriceName()
    {
        $notes = unserialize($this->getField('NOTES'), ['allowed_classes' => true]);
        $price = \Bitrix\Catalog\GroupTable::getById($this->getField('PRICE_TYPE_ID'))->fetch();
        $notes['PRICE_NAME'] = $price['NAME'];

        $this->setField('NOTES', serialize($notes));
    }

    public function setBasketItemPropertyArticle()
    {
        $notes = unserialize($this->getField('NOTES'), ['allowed_classes' => true]);
        $this->setProperty(Loc::getMessage('WC_SALE_ARTICLE'), 'ARTICLE', $notes['ARTICLE']);
    }

    public function prepareBasketItemFields()
    {
        // todo универсальный вариант под торговые предложения и товары
        return [];
    }

    public static function getIblockElementInfo($productId)
    {
        // todo
        return [];
    }

    /**
     * @param string $math = 'plus' | 'minus'
     */
    public function mathBasketItemQuantity(string $math)
    {
        $ratio = \WC\Catalog\Tools::getProductRatio($this->getProductId());

        if (!$quantity = $this->getQuantity()) {
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

        $this->setBasketItemQuantity($quantity);
    }

    public function setBasketItemQuantity($quantity = 0.0)
    {
        $ratio = \WC\Catalog\Tools::getProductRatio($this->getProductId());

        // todo Проверить остатки, установить количество по остаткам

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

        $this->setField('QUANTITY', $quantity);
    }
}