<?php


namespace WC\Sale;


class Order extends \Bitrix\Sale\Order
{
    public function getInfo(): array
    {
        $basket = $this->getBasket();
        $info = $basket->getInfo();
        // todo delivery info
        return $info;
    }

    public function getRestrictedProperties(): array
    {
        /** @var \Bitrix\Sale\PropertyValue $orderProperty */

        $restrictedProperties = [];

        foreach ($this->getPropertyCollection() as $orderProperty) {
            if ($orderProperty->isUtil()) {
                continue;
            }

            $propertyRelations = $orderProperty->getRelations();

            $paySystemCheck = null;
            $deliveryCheck = null;

            if (is_array($propertyRelations) && !empty($propertyRelations)) {
                foreach ($propertyRelations as $propertyRelation) {
                    if (!$paySystemCheck && $propertyRelation['ENTITY_TYPE'] == 'P') {
                        $paySystemCheck = in_array($propertyRelation['ENTITY_ID'], $this->getPaySystemIdList(), true);
                    }

                    if (!$deliveryCheck && $propertyRelation['ENTITY_TYPE'] == 'D') {
                        $deliveryCheck = in_array($propertyRelation['ENTITY_ID'], $this->getDeliveryIdList(), true);
                    }
                }
            }

            if ((is_null($paySystemCheck) || $paySystemCheck) && (is_null($deliveryCheck) || $deliveryCheck)) {
                $restrictedProperties[] = $orderProperty;
            }
        }

        return $restrictedProperties;
    }
}
