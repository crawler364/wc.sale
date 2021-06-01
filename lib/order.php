<?php


namespace WC\Sale;


use Bitrix\Sale\Fuser;

class Order extends \Bitrix\Sale\Order
{
    public function getInfo(): array
    {
        $basket = $this->getBasket();
        $info = $basket->getInfo();
        // todo delivery info
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

    public function getRestrictedProperties(\Bitrix\Sale\PropertyValueCollection $propertyValueCollection): array
    {
        /** @var \Bitrix\Sale\PropertyValue $property */

        $restrictedProperties = [];

        foreach ($propertyValueCollection as $property) {
            if ($property->isUtil()) {
                continue;
            }

            $propertyRelations = $property->getRelations();

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
                $restrictedProperties[] = $property;
            }
        }

        return $restrictedProperties;
    }

    public function getRestrictedDeliveries(\Bitrix\Sale\ShipmentCollection $shipmentCollection): array
    {
        $shipment = \Bitrix\Sale\Shipment::create($shipmentCollection);
        $restrictedDeliveries = \Bitrix\Sale\Delivery\Services\Manager::getRestrictedList(
            $shipment,
            \Bitrix\Sale\Delivery\Restrictions\Manager::MODE_CLIENT
        );

        return array_values($restrictedDeliveries);
    }

    public function getRestrictedPaySystems(\Bitrix\Sale\PaymentCollection $paymentCollection): array
    {
        $payment = \Bitrix\Sale\Payment::create($paymentCollection);
        $restrictedPaySystems = \Bitrix\Sale\PaySystem\Manager::getListWithRestrictions($payment);

        return array_values($restrictedPaySystems);
    }
}
