<?php


namespace WC\Sale;


use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Order;
use WC\Main\Result;
use Bitrix\Main\Context;

class OrderHandler
{
    /** @var BasketHandler */
    protected $basketHandler = BasketHandler::class;

    public function __construct(Order $order, array $orderData = null)
    {
        $this->result = new Result();
        Loc::loadMessages(__FILE__);

        $this->order = $order;
        $this->orderData = $orderData ?? Context::getCurrent()->getRequest()->get('data');
    }

    public function saveOrder(): \Bitrix\Sale\Result
    {
        // todo $this->checkOrderData();

        $this->setPersonType();
        $this->setProperties();
        $this->setBasket();
        $this->setShipment();
        $this->setPayment();

        return $this->order->save();
    }

    protected function setPersonType()
    {
        if ($this->orderData['PERSON_TYPE_ID']) {
            $personTypeId = $this->orderData['PERSON_TYPE_ID'];
        } elseif ($personType = \Bitrix\Sale\PersonType::getList(['order' => ['SORT' => 'ASC']])->fetch()) {
            $personTypeId = $personType['ID'];
        } else {
            $this->result->addError('WC_ORDER_NULL_PERSON_TYPE');
        }

        $this->order->setPersonTypeId($personTypeId);
    }

    protected function setProperties()
    {
        /** @var \Bitrix\Sale\PropertyValue $orderProperty */
        foreach ($this->order->getPropertyCollection() as $orderProperty) {
            if ($orderProperty->isUtil()) {
                continue;
            }

            $propertyValue = $this->orderData[$orderProperty->getField('CODE')] ?? $orderProperty->getProperty()['DEFAULT_VALUE'];

            $orderProperty->setValue($propertyValue);
        }
    }

    protected function setBasket()
    {
        $basket = $this->basketHandler::getBasket($this->order->getUserId());
        $this->order->setBasket($basket);
    }

    protected function getPersonTypes(): array
    {
        $obPersonTypes = \Bitrix\Sale\PersonType::getList([
            'order' => ['SORT' => 'ASC'],
        ]);
        while ($personType = $obPersonTypes->fetch()) {
            $personTypes[] = $personType;
        }

        foreach ($personTypes as &$personType) {
            if ($this->orderData['PERSON_TYPE_ID'] == $personType['ID']) {
                $personType['CHECKED'] = true;
            }
        }

        return $personTypes;
    }

    protected function getProperties(): array
    {
        /** @var \Bitrix\Sale\PropertyValue $property */
        foreach ($this->order->getPropertyCollection() as $orderProperty) {
            if ($orderProperty->isUtil()) {
                continue;
            }

            $property = $orderProperty->getProperty();
            $property['VALUE'] = $orderProperty->getValue();

            $properties[] = $property;
        }

        return $properties;
    }

    protected function getProductList(): array
    {
        $basket = $this->order->getBasket();
        return $basket->getInfo()['ITEMS'];
    }

    protected function setShipment()
    {
        $shipmentCollection = $this->order->getShipmentCollection();
        $shipment = $shipmentCollection->createItem(/*\Bitrix\Sale\Delivery\Services\Manager::getObjectById($deliveryId)*/);
        $shipmentItemCollection = $shipment->getShipmentItemCollection();
        $shipment->setField('CURRENCY', $this->order->getCurrency());

        foreach ($this->order->getBasket() as $item) {
            /** @var \Bitrix\Sale\ShipmentItem $shipmentItem */
            $shipmentItem = $shipmentItemCollection->createItem($item);
            $shipmentItem->setQuantity($item->getQuantity());
        }


        /** @var \Bitrix\Sale\Delivery\Services\Base $arDeliveryServiceAll */
        $arDeliveryServiceAll = \Bitrix\Sale\Delivery\Services\Manager::getRestrictedObjectsList($shipment);
        foreach ($arDeliveryServiceAll as $delivery) {
            $deliveries[] = \Bitrix\Sale\Delivery\Services\Manager::getById($delivery->getId());
        }

        $this->orderData['DELIVERY_ID'] = $this->orderData['DELIVERY_ID'] ?? $deliveries[0]['ID'];

        $shipment->setField('DELIVERY_ID', $this->orderData['DELIVERY_ID']);
        $shipmentCollection = $shipment->getCollection();
        $shipmentCollection->calculateDelivery();
    }

    protected function getDeliveries(): array
    {
        $shipmentCollection = $this->order->getShipmentCollection();
        $arDeliveryServiceAll = \Bitrix\Sale\Delivery\Services\Manager::getRestrictedObjectsList($shipmentCollection[1]);
        foreach ($arDeliveryServiceAll as $delivery) {
            $deliveries[] = \Bitrix\Sale\Delivery\Services\Manager::getById($delivery->getId());
        }
        foreach ($deliveries as &$delivery) {
            if ($this->orderData['DELIVERY_ID'] == $delivery['ID']) {
                $delivery['CHECKED'] = true;
                break;
            }
        }

        return $deliveries;
    }

    protected function setPayment()
    {
        $paymentCollection = $this->order->getPaymentCollection();
        $payment = $paymentCollection->createItem(\Bitrix\Sale\PaySystem\Manager::getObjectById($paySystemId));
        $payment->setField('SUM', $this->order->getPrice());
        $payment->setField('CURRENCY', $this->order->getCurrency());
        $payment->setField('PAY_SYSTEM_ID', $this->orderData['PAY_SYSTEM_ID']);
    }

    protected function getPaySystems(): array
    {

        $paymentCollection = $this->order->getPaymentCollection();
        $arPaySystemServices = \Bitrix\Sale\PaySystem\Manager::getListWithRestrictions($paymentCollection[0]);

        foreach ($arPaySystemServices as $paySystem) {
            $paySystems[] = \Bitrix\Sale\PaySystem\Manager::getById($paySystem['ID']);
        }

        $paySystemId = $this->orderData['PAY_SYSTEM_ID'] ?? $paySystems[0]['ID'];

        foreach ($paySystems as &$paySystem) {
            if ($paySystemId == $paySystem['ID']) {
                $paySystem['CHECKED'] = true;
                break;
            }
        }

        return $paySystems;

    }

    public function processOrder(): Result
    {
        if (!$this->result->isSuccess()) {
            return $this->result;
        }

        // todo $this->checkOrderData();

        $this->setPersonType();
        $this->setProperties();
        $this->setBasket();
        $this->setShipment();
        $this->setPayment();

        $personTypes = $this->getPersonTypes();
        $properties = $this->getProperties();
        $productList = $this->getProductList();
        $deliveries = $this->getDeliveries();
        $paySystems = $this->getPaySystems();


        // $r = $order->getPaymentCollection();


        $data = [
            'PERSON_TYPES' => $personTypes,
            'PROPERTIES' => $properties,
            'DELIVERIES' => $deliveries,
            'PAY_SYSTEMS' => $paySystems,
            'PRODUCT_LIST' => $productList,
            'INFO' => $this->order->getInfo(),
        ];

        $this->result->setData($data);

        return $this->result;
    }

    public static function createOrder(int $userId = null): Order
    {
        if (!$userId) {
            global $USER;
            $userId = $USER->GetID();
        }

        $siteId = \WC\Main\Tools::getSiteId();

        return Order::create($siteId, $userId);
    }

    protected function addOrder()
    {

    }

    protected function updateOrder()
    {
        // todo
    }

}
