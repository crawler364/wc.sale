<?php


namespace WC\Sale;


use WC\Core\Helpers\Catalog;

class Basket extends \Bitrix\Sale\Basket
{
    public function getFieldValuesFormatted(): array
    {
        $fields = $this->getFieldValues();

        $fields['WEIGHT_FORMATTED'] = Catalog::formatWeight($fields['WEIGHT']);
        $fields['VAT_SUM_FORMATTED'] = Catalog::formatPrice($fields['VAT_SUM']);
        $fields['BASE_PRICE_FORMATTED'] = Catalog::formatPrice($fields['BASE_PRICE']);
        $fields['DISCOUNT_PRICE_FORMATTED'] = Catalog::formatPrice($fields['DISCOUNT_PRICE']);
        $fields['PRICE_FORMATTED'] = Catalog::formatPrice($fields['PRICE']);

        return $fields;
    }

    public function getItemsList(): array
    {
        /** @var BasketItem $basketItem */

        $itemsList = [];

        foreach ($this->getBasketItems() as $basketItem) {
            $itemsList[] = $basketItem->getFieldValuesFormatted();
        }

        return $itemsList;
    }

    public function getFieldValues(): array
    {
        return [
            'COUNT' => $this->count(),
            'WEIGHT' => $this->getWeight(),
            'VAT_SUM' => $this->getVatSum(),
            'BASE_PRICE' => $this->getBasePrice(),
            'DISCOUNT_PRICE' => $this->getDiscount(),
            'PRICE' => $this->getPrice(),
        ];
    }

    public function getDiscount()
    {
        return $this->getBasePrice() - $this->getPrice();
    }

    /**
     * @param array $param = ['PRODUCT_ID'|'PRODUCT_XML_ID'|'SORT']
     * @return BasketItem|null
     */
    public function getItemBy(array $param): ?BasketItem
    {
        foreach ($this->getBasketItems() as $item) {
            $key = array_keys($param)[0];
            switch ($key) {
                case 'PRODUCT_ID':
                    if ($param[$key] === $item->getProductId()) {
                        return $item;
                    }
                    break;
                case 'PRODUCT_XML_ID':
                    if ($param[$key] === $item->getField('PRODUCT_XML_ID')) {
                        return $item;
                    }
                    break;
                case 'SORT':
                    if ($param[$key] === $item->getField('SORT')) {
                        return $item;
                    }
                    break;
            }
        }

        return null;
    }
}
