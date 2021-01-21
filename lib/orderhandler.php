<?php


namespace WC\Sale;


use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Order;
use WC\Main\Result;
use Bitrix\Main\Context;

class OrderHandler
{
    public function __construct(Order $order, array $orderData = null)
    {
        $this->result = new Result();
        Loc::loadMessages(__FILE__);

        $this->order = $order;
        $this->orderData = $orderData ?: $this->getOrderDataFromRequest();
    }

    protected function getOrderDataFromRequest()
    {
        $context = Context::getCurrent();
        $request = $context->getRequest();
        return $request->get('data');
    }

    protected function addOrder()
    {

    }

    protected function updateOrder()
    {
        // todo
    }


    protected function initPersonType(): array
    {
        $obPersonTypes = \Bitrix\Sale\PersonType::getList([
            'order' => ['SORT' => 'ASC'],
        ]);
        while ($personType = $obPersonTypes->fetch()) {
            $personTypes[] = $personType;
        }

        $personTypeId = $this->orderData['PERSON_TYPE_ID'] ?: $personTypes[0]['ID'];

        foreach ($personTypes as &$personType) {
            if ($personTypeId == $personType['ID']) {
                $personType['CHECKED'] = true;
                $this->order->setPersonTypeId($personTypeId);
            }
        }

        return $personTypes;
    }

    protected function initProperties(): array
    {
        //$properties = $order->loadPropertyCollection();
        /** @var \Bitrix\Sale\PropertyValue $property */
        foreach ($this->order->getPropertyCollection() as $property) {
            if ($property->isUtil()) {
                continue;
            }
            $arProperty = $property->getProperty();
            $arProperty['VALUE'] = $this->orderData[$arProperty['CODE']] ?? $arProperty['DEFAULT_VALUE'];

            $properties[] = $arProperty;
        }

        return $properties;
    }

    protected function initShipment(): \Bitrix\Sale\Shipment
    {
        $shipmentCollection = $this->order->getShipmentCollection();
        $shipment = $shipmentCollection->createItem();
        $shipmentItemCollection = $shipment->getShipmentItemCollection();
        $shipment->setField('CURRENCY', $this->order->getCurrency());

        foreach ($this->order->getBasket() as $item) {
            /** @var \Bitrix\Sale\ShipmentItem $shipmentItem */
            $shipmentItem = $shipmentItemCollection->createItem($item);
            $shipmentItem->setQuantity($item->getQuantity());
        }


        return $shipment;
    }

    protected function initDelivery($shipment): array
    {
        /** @var \Bitrix\Sale\Delivery\Services\Base $arDeliveryServiceAll */
        $arDeliveryServiceAll = \Bitrix\Sale\Delivery\Services\Manager::getRestrictedObjectsList($shipment);
        foreach ($arDeliveryServiceAll as $delivery) {
            $deliveries[] = \Bitrix\Sale\Delivery\Services\Manager::getById($delivery->getId());
        }

        $deliveryId = $this->orderData['DELIVERY_ID'] ?? $deliveries[0]['ID'];

        foreach ($deliveries as &$delivery) {
            if ($deliveryId == $delivery['ID']) {
                $delivery['CHECKED'] = true;
            }
        }

        return $deliveries;
    }

    public function processOrder(): Result
    {
        $personTypes = $this->initPersonType();

        $properties = $this->initProperties();

        $this->order->setBasket(\WC\Sale\BasketHandler::getCurrentUserBasket());

        $shipment = $this->initShipment();
        $deliveries = $this->initDelivery($shipment);

        // $r = $order->getPaymentCollection();


        $data = [
            'PERSON_TYPES' => $personTypes,
            'PROPERTIES' => $properties,
            'DELIVERIES' => $deliveries,
        ];

        $this->result->setData($data);

        return $this->result;
    }

    public function saveOrder(): \Bitrix\Sale\Result
    {
        return $this->order->save();
    }

    public static function createOrder(): Order
    {
        global $USER;
        $siteId = \WC\Main\Tools::getSiteId();
        $userId = $USER->GetID();
        return Order::create($siteId, $userId);
    }
}