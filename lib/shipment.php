<?php


namespace WC\Sale;


class Shipment extends \Bitrix\Sale\Shipment
{
    public function fill(Basket $basket): void
    {
        /**
         * @var \Bitrix\Sale\ShipmentItemCollection $shipmentItemCollection
         * @var \Bitrix\Sale\ShipmentItem $shipmentItem
         */

        $shipmentItemCollection = $this->getShipmentItemCollection();

        foreach ($basket as $basketItem) {
            if ($shipmentItem = $shipmentItemCollection->createItem($basketItem)) {
                $shipmentItem->setQuantity($basketItem->getQuantity());
            }
        }
    }
}
