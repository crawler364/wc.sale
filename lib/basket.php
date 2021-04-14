<?php


namespace WC\Sale;


use WC\Core\Helpers\Catalog;

class Basket extends \Bitrix\Sale\Basket
{
    public function getData(): array
    {
        return [
            'INFO' => $this->getInfo(),
            'ITEMS' => $this->getItemsList(),
            'REMOVED_ITEMS' => $this->getRemovedItemsList(),
        ];
    }

    public function getInfo(): array
    {
        $info = [];
        $info['COUNT'] = $this->count();
        $info['WEIGHT'] = $this->getWeight();
        $info['VAT'] = $this->getVatSum();
        $info['BASE_PRICE'] = $this->getBasePrice();
        $info['DISCOUNT_PRICE'] = $this->getDiscount();
        $info['PRICE'] = $this->getPrice();

        $info['WEIGHT_FORMATTED'] = Catalog::formatWeight($info['WEIGHT']);
        $info['VAT_FORMATTED'] = Catalog::formatPrice($info['VAT']);
        $info['BASE_PRICE_FORMATTED'] = Catalog::formatPrice($info['BASE_PRICE']);
        $info['DISCOUNT_PRICE_FORMATTED'] = Catalog::formatPrice($info['DISCOUNT_PRICE']);
        $info['PRICE_FORMATTED'] = Catalog::formatPrice($info['PRICE']);

        return $info;
    }

    public function getRemovedItemsList(): ?array
    {
        $removedItemsList = [];
        // todo
        return $removedItemsList;
    }

    public function getItemsList(): array
    {
        /** @var BasketItem $basketItem */

        $itemsList = [];

        foreach ($this->getBasketItems() as $basketItem) {
            $itemsList[] = $basketItem->getInfo();
        }

        return $itemsList;
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

    public function getDiscount()
    {
        return $this->getBasePrice() - $this->getPrice();
    }
}
