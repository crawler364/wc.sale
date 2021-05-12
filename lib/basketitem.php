<?php


namespace WC\Sale;


use Bitrix\Catalog\GroupTable;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\BasketPropertyItem;
use WC\Core\Helpers\Catalog;

class BasketItem extends \Bitrix\Sale\BasketItem
{
    public function getInfo(): array
    {
        $info = $this->getFieldValues();

        $info['QUANTITY'] = $this->getQuantity();
        $info['BASE_PRICE_SUM'] = $info['BASE_PRICE'] * $info['QUANTITY'];
        $info['PRICE_SUM'] = $info['PRICE'] * $info['QUANTITY'];
        $info['DISCOUNT_PRICE_SUM'] = $info['DISCOUNT_PRICE'] * $info['QUANTITY'];  // DISCOUNT_PRICE - величина скидки, а не цена со скидкой

        $info['WEIGHT_FORMATTED'] = Catalog::formatWeight($info['WEIGHT']);
        $info['PRICE_FORMATTED'] = Catalog::formatPrice($info['PRICE'], $info['CURRENCY']);
        $info['PRICE_SUM_FORMATTED'] = Catalog::formatPrice($info['PRICE_SUM'], $info['CURRENCY']);
        $info['BASE_PRICE_FORMATTED'] = Catalog::formatPrice($info['BASE_PRICE'], $info['CURRENCY']);
        $info['BASE_PRICE_SUM_FORMATTED'] = Catalog::formatPrice($info['BASE_PRICE_SUM'], $info['CURRENCY']);
        $info['DISCOUNT_PRICE_FORMATTED'] = Catalog::formatPrice($info['DISCOUNT_PRICE'], $info['CURRENCY']);
        $info['DISCOUNT_PRICE_SUM_FORMATTED'] = Catalog::formatPrice($info['DISCOUNT_PRICE_SUM'], $info['CURRENCY']);

        $info['IBLOCK_ELEMENT'] = $this->getIblockElementInfo();

        return $info;
    }

    public function setProperty($name, $code, $value): void
    {
        /** @var BasketPropertiesCollection $propertyCollection */
        /** @var BasketPropertyItem $property */

        if ($propertyCollection = $this->getPropertyCollection()) {
            if (!$property = $propertyCollection->getItemByCode($code)) {
                $property = $propertyCollection->createItem();
            }

            $property->setFields([
                'NAME' => $name,
                'CODE' => $code,
                'VALUE' => $value,
            ]);
        }
    }

    public function setNotes($field): void
    {
        $notes = unserialize($this->getField('NOTES'), ['allowed_classes' => true]);

        switch ($field) {
            case 'PRICE_CODE':
                $priceTypeId = $this->getField('PRICE_TYPE_ID');
                $notes[$field] = $priceTypeId > 0 ? GroupTable::getById($priceTypeId)->fetch()['NAME'] : null;
                break;
        }

        $this->setField('NOTES', serialize($notes));
    }

    public function getIblockElementInfo(): array
    {
        // todo
        return [];
    }

    /**
     * @param string $action
     * @return float|int
     * @throws ArgumentNullException
     */
    public function mathQuantity(string $action)
    {
        $ratio = Catalog::getProductRatio($this->getProductId());

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
     * @throws ArgumentNullException
     */
    public function checkQuantity($quantity)
    {
        $ratio = Catalog::getProductRatio($this->getProductId());

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
