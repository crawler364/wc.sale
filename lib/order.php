<?php


namespace WC\Sale;


use Bitrix\Main\ArgumentException;
use Bitrix\Sale\Fuser;
use Bitrix\Sale\PropertyValue;
use WC\Core\Helpers\Catalog;

class Order extends \Bitrix\Sale\Order
{
    public function getFieldValuesFormatted(): array
    {
        $fields = $this->getFieldValues();

        $fields['PRICE_FORMATTED'] = Catalog::formatPrice($fields['PRICE']);
        $fields['PRICE_DELIVERY_FORMATTED'] = Catalog::formatPrice($fields['PRICE_DELIVERY']);
        $fields['SUM_PAID_FORMATTED'] = Catalog::formatPrice($fields['SUM_PAID']);

        return $fields;
    }

    /**
     * @return null|int
     * @throws ArgumentException
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
        /** @var PropertyValue $property */

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

    public function getPropertyByCode($code)
    {
        $pc = $this->getPropertyCollection();
        $personTypeId = $this->getPersonTypeId();

        foreach ($pc as $property) {
            if ($property->getPersonTypeId() == $personTypeId && $property->getField('CODE') === $code) {
                return $property;
            }
        }

        return null;
    }
}
