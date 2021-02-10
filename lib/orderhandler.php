<?php


namespace WC\Sale;


use WC\Main\Result;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Restrictions\Manager;
use Bitrix\Sale\Delivery\Services;

class OrderHandler
{
    /** @var Order */
    protected $order;
    /** @var BasketHandler */
    protected $basketHandler = BasketHandler::class;

    public function __construct(Order $order, array $orderData = null)
    {
        $this->result = new Result();
        Loc::loadMessages(__FILE__);

        $this->order = $order;
        $this->orderData = $orderData ?? Context::getCurrent()->getRequest()->get('data');
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

    protected function setShipment()
    {
        /** @var \Bitrix\Sale\ShipmentItem $shipmentItem */

        $shipmentCollection = $this->order->getShipmentCollection();

        if ($this->orderData['PAY_SYSTEM_ID']) {
            $deliveryId = $this->orderData['DELIVERY_ID'];
        } else {
            $shipment = \Bitrix\Sale\Shipment::create($shipmentCollection);
            $restrictedDeliveries = Services\Manager::getRestrictedList($shipment, Manager::MODE_CLIENT);
            $deliveryId = $restrictedDeliveries[array_keys($restrictedDeliveries)[0]]['ID'];
        }

        $shipment = $shipmentCollection->createItem(Services\Manager::getObjectById($deliveryId));
        $shipmentItemCollection = $shipment->getShipmentItemCollection();

        foreach ($this->order->getBasket() as $basketItem) {
            $shipmentItem = $shipmentItemCollection->createItem($basketItem);
            $shipmentItem->setQuantity($basketItem->getQuantity());
        }
    }

    protected function setPayment()
    {
        $paymentCollection = $this->order->getPaymentCollection();

        if ($this->orderData['PAY_SYSTEM_ID']) {
            $paySystemId = $this->orderData['PAY_SYSTEM_ID'];
        } else {
            $payment = \Bitrix\Sale\Payment::create($paymentCollection);
            $restrictedPaySystems = \Bitrix\Sale\PaySystem\Manager::getListWithRestrictions($payment);
            $paySystemId = $restrictedPaySystems[array_keys($restrictedPaySystems)[0]]['ID'];
        }

        $payment = $paymentCollection->createItem(\Bitrix\Sale\PaySystem\Manager::getObjectById($paySystemId));
        $payment->setField('SUM', $this->order->getPrice());
        $payment->setField('CURRENCY', $this->order->getCurrency());
    }

    protected function getPersonTypes(): array
    {
        $orderPersonTypeId = $this->order->getPersonTypeId();

        $obPersonTypes = \Bitrix\Sale\PersonType::getList([
            'order' => ['SORT' => 'ASC'],
            'filter' => ['ACTIVE' => 'Y'],
        ]);
        while ($personType = $obPersonTypes->fetch()) {
            if ($orderPersonTypeId == $personType['ID']) {
                $personType['CHECKED'] = true;
            }
            $personTypes[] = $personType;
        }

        return $personTypes;
    }

    protected function getProperties(): array
    {
        /** @var \Bitrix\Sale\PropertyValue $orderProperty */

        $orderProperties = $this->order->getRestrictedProperties();

        foreach ($orderProperties as $orderProperty) {
            $property = $orderProperty->getProperty();
            $property['VALUE'] = $orderProperty->getValue();
            $properties[] = $property;
        }

        return $properties;
    }

    protected function getProductList(): array
    {
        $basket = $this->order->getBasket();
        return $basket->getItemsList();
    }

    protected function getDeliveries(): array
    {
        $shipmentCollection = $this->order->getShipmentCollection();
        $restrictedDeliveries = Services\Manager::getRestrictedObjectsList($shipmentCollection[1]);
        $deliveryId = $shipmentCollection[1]->getDeliveryId();

        foreach ($restrictedDeliveries as $restrictedDelivery) {
            $delivery = Services\Manager::getById($restrictedDelivery->getId());

            if ($deliveryId == $delivery['ID']) {
                $delivery['CHECKED'] = true;
            }

            $deliveries[] = $delivery;
        }

        return $deliveries;
    }

    protected function getPaySystems(): array
    {
        $paymentCollection = $this->order->getPaymentCollection();
        $restrictedPaySystems = \Bitrix\Sale\PaySystem\Manager::getListWithRestrictions($paymentCollection[0]);
        $paySystemId = $paymentCollection[0]->getPaymentSystemId();

        foreach ($restrictedPaySystems as $restrictedPaySystem) {
            $paySystem = \Bitrix\Sale\PaySystem\Manager::getById($restrictedPaySystem['ID']);

            if ($paySystemId == $paySystem['ID']) {
                $paySystem['CHECKED'] = true;
            }

            $paySystems[] = $paySystem;
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

    protected function addOrder()
    {
        // todo
    }

    protected function updateOrder()
    {
        // todo
    }

    public static function createOrder(int $userId = null): Order
    {
        if ($userId == null) {
            global $USER;
            $userId = $USER->GetID();
        }

        $siteId = \WC\Core\Helpers\Main::getSiteId();

        return Order::create($siteId, $userId);
    }
}
