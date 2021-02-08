<?php


namespace WC\Sale;


class Order extends \Bitrix\Sale\Order
{
    public function getInfo(): array
    {
        $basket = $this->getBasket();
        $basketInfo = $basket->getInfo();

        return $basketInfo['INFO'];
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

    protected function checkRelatedProperty($property, $paySystemIdList, $deliveryIdList): bool
    {
        $paySystemCheck = null;
        $deliveryCheck = null;

        if (is_array($property['RELATION']) && !empty($property['RELATION'])) {
            foreach ($property['RELATION'] as $relation) {
                if (!$paySystemCheck && $relation['ENTITY_TYPE'] == 'P') {
                    $paySystemCheck = in_array($relation['ENTITY_ID'], $paySystemIdList, true);
                }

                if (!$deliveryCheck && $relation['ENTITY_TYPE'] == 'D') {
                    $deliveryCheck = in_array($relation['ENTITY_ID'], $deliveryIdList, true);
                }
            }
        }

        return ((is_null($paySystemCheck) || $paySystemCheck) && (is_null($deliveryCheck) || $deliveryCheck));
    }
}
