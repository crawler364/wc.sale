<?php


namespace WC\Sale;


class BasketPropertiesCollection extends \Bitrix\Sale\BasketPropertiesCollection
{
    public function getItemByCode($code)
    {
        foreach ($this->collection as $propertyItem) {
            if ($propertyItem->getField('CODE') === $code) {
                return $propertyItem;
            }
        }

        return null;
    }
}
