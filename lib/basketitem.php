<?php


namespace WC\Sale;


use Bitrix\Catalog\GroupTable;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\BasketPropertyItem;
use WC\Core\Helpers\Catalog;

class BasketItem extends \Bitrix\Sale\BasketItem
{
    public function getFieldValuesFormatted(): array
    {
        $fields = $this->getFieldValues();

        $fields['QUANTITY'] = $this->getQuantity();
        $fields['BASE_PRICE_SUM'] = $fields['BASE_PRICE'] * $fields['QUANTITY'];
        $fields['PRICE_SUM'] = $fields['PRICE'] * $fields['QUANTITY'];
        $fields['DISCOUNT_PRICE_SUM'] = $fields['DISCOUNT_PRICE'] * $fields['QUANTITY'];  // DISCOUNT_PRICE - величина скидки, а не цена со скидкой

        $fields['WEIGHT_FORMATTED'] = Catalog::formatWeight($fields['WEIGHT']);
        $fields['PRICE_FORMATTED'] = Catalog::formatPrice($fields['PRICE'], $fields['CURRENCY']);
        $fields['PRICE_SUM_FORMATTED'] = Catalog::formatPrice($fields['PRICE_SUM'], $fields['CURRENCY']);
        $fields['BASE_PRICE_FORMATTED'] = Catalog::formatPrice($fields['BASE_PRICE'], $fields['CURRENCY']);
        $fields['BASE_PRICE_SUM_FORMATTED'] = Catalog::formatPrice($fields['BASE_PRICE_SUM'], $fields['CURRENCY']);
        $fields['DISCOUNT_PRICE_FORMATTED'] = Catalog::formatPrice($fields['DISCOUNT_PRICE'], $fields['CURRENCY']);
        $fields['DISCOUNT_PRICE_SUM_FORMATTED'] = Catalog::formatPrice($fields['DISCOUNT_PRICE_SUM'], $fields['CURRENCY']);

        $fields['IBLOCK_ELEMENT'] = $this->getIblockElementInfo();

        return $fields;
    }

    public function getIblockElementInfo(): array
    {
        // todo
        return [];
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
