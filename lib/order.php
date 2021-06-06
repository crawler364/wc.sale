<?php


namespace WC\Sale;


use Bitrix\Sale\Fuser;
use WC\Core\Helpers\Catalog;

class Order extends \Bitrix\Sale\Order
{
    public function getInfo(): array
    {
        $info = $this->getFieldValues();

        $info['PRICE_FORMATTED'] = Catalog::formatPrice($info['PRICE']);
        $info['PRICE_DELIVERY_FORMATTED'] = Catalog::formatPrice($info['PRICE_DELIVERY']);
        $info['SUM_PAID_FORMATTED'] = Catalog::formatPrice($info['SUM_PAID']);

        return $info;
    }

    /**
     * @return null|int
     */
    public function getFUserId(): ?int
    {
        if ($userId = $this->getUserId()) {
            return Fuser::getIdByUserId($userId);
        }

        return null;
    }

    public function getRestrictedProperties(): array
    {
        /** @var \Bitrix\Sale\PropertyValue $property */

        $restrictedProperties = [];
        $propertyCollection = $this->getPropertyCollection();

        foreach ($propertyCollection as $property) {
            if ($property->isUtil()) {
                continue;
            }

            $propertyRelations = $property->getRelations();

            $paySystemCheck = null;
            $deliveryCheck = null;

            if (is_array($propertyRelations) && !empty($propertyRelations)) {
                foreach ($propertyRelations as $propertyRelation) {
                    if (!$paySystemCheck && $propertyRelation['ENTITY_TYPE'] == 'P') {
                        $paySystemCheck = in_array($propertyRelation['ENTITY_ID'], $this->getPaySystemIdList(), false);
                    }

                    if (!$deliveryCheck && $propertyRelation['ENTITY_TYPE'] == 'D') {
                        $deliveryCheck = in_array($propertyRelation['ENTITY_ID'], $this->getDeliveryIdList(), false);
                    }
                }
            }

            if ((is_null($paySystemCheck) || $paySystemCheck) && (is_null($deliveryCheck) || $deliveryCheck)) {
                $restrictedProperties[] = $property;
            }
        }

        return $restrictedProperties;
    }
}
