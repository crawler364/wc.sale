<?php


namespace WC\Sale;


class BasketItem extends \Bitrix\Sale\BasketItem
{
    /**
     * @var basketHandler $basketHandler
     */
    public $basketHandler = basketHandler::class;

    public function getInfo()
    {
        \Bitrix\Main\Loader::includeModule('iblock');

        $productId = $this->getProductId();

        $info['ELEMENT'] = $this->basketHandler::getIblockElementInfo($productId);

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