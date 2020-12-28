<?php


namespace WC\Sale;


class Basket extends \Bitrix\Sale\Basket
{
    public $basketHandler = basketHandler::class;

    public function getInfo()
    {
        // $items
        foreach ($this->getBasketItems() as $basketItem) {
            $items[] = $basketItem->getInfo();
        }

        // todo $removedItems
        $removedItems = [];

        // $info
        $info['WEIGHT'] = (string)$this->getWeight();
        $info['WEIGHT_FORMATTED'] = \WC\Catalog\Tools::formatWeight($info['WEIGHT']);
        $info['PRICE'] = (string)$this->getPrice();
        $info['PRICE_FORMATTED'] = \WC\Currency\Tools::format($this->getPrice());
        $info['VAT'] = (string)$this->getVatSum();
        $info['VAT_FORMATTED'] = \WC\Currency\Tools::format($info['VAT']);
        $info['COUNT'] = (string)$this->count();
        $info['DISCOUNT'] = (string)($this->getBasePrice() - (string)$this->getPrice());
        $info['DISCOUNT_FORMATTED'] = \WC\Currency\Tools::format($info['DISCOUNT']);

        return ['INFO' => $info, 'ITEMS' => $items, 'REMOVED_ITEMS' => $removedItems];
    }

    /**
     * @param array $param = ['PRODUCT_ID'|'PRODUCT_XML_ID'|'SORT']
     * @return \Bitrix\Sale\BasketItemBase|null
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