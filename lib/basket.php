<?php


namespace WC\Sale;


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
        $info['COUNT'] = (string)$this->count();
        $info['WEIGHT'] = (string)$this->getWeight();
        $info['WEIGHT_FORMATTED'] = \WC\Catalog\Tools::formatWeight($info['WEIGHT']);
        $info['VAT'] = (string)$this->getVatSum();
        $info['VAT_FORMATTED'] = \WC\Currency\Tools::format($info['VAT']);
        $info['PRICE_BASE'] = (string)$this->getBasePrice();
        $info['PRICE_BASE_FORMATTED'] = \WC\Currency\Tools::format($info['PRICE_BASE']);
        $info['DISCOUNT'] = (string)($this->getBasePrice() - (string)$this->getPrice());
        $info['DISCOUNT_FORMATTED'] = \WC\Currency\Tools::format($info['DISCOUNT']);
        $info['PRICE'] = (string)($this->getPrice());
        $info['PRICE_FORMATTED'] = \WC\Currency\Tools::format($info['PRICE']);

        return $info;
    }

    /** @noinspection PhpUnnecessaryLocalVariableInspection
     * @noinspection OneTimeUseVariablesInspection
     */
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
                    if ($param[$key] == $item->getProductId()) {
                        return $item;
                    }
                    break;
                case 'PRODUCT_XML_ID':
                    if ($param[$key] == $item->getField('PRODUCT_XML_ID')) {
                        return $item;
                    }
                    break;
                case 'SORT':
                    if ($param[$key] == $item->getField('SORT')) {
                        return $item;
                    }
                    break;
            }
        }

        return null;
    }
}
