<?php


namespace WC\Sale\Handlers\Internals;


use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\Fuser;
use WC\Core\Bitrix\Main\Result;
use WC\Sale\Basket;
use WC\Sale\Handlers\Basket as BasketHandler;
use WC\Sale\Order;

Loc::loadMessages(__FILE__);

abstract class OrderBase implements OrderInterface
{
    /** @var Result */
    protected $result;
    /** @var Order */
    protected $order;
    /** @var BasketHandler */
    protected $basketHandler = BasketHandler::class;
    protected $data;
    protected $params;

    public function __construct(Order $order, array $data = [], array $params = [])
    {
        $this->result = new Result();
        $this->order = $order;
        $this->data = $data;
        $this->params = $params;
    }

    /**
     * @param int|null $userId
     * @return Order|\Bitrix\Sale\Order
     */
    public static function createOrder($userId = null)
    {
        $siteId = \WC\Core\Helpers\Main::getSiteId();

        return Order::create($siteId, $userId);
    }

    public function processOrder(): Result
    {
        $this->setPersonType();
        $personTypes = $this->getPersonTypes();
        $this->setBasket();
        $productsList = $this->getProductsList();
        $this->setLocation();
        $location = $this->getLocation();
        $this->setShipment();
        $deliveries = $this->getDeliveries();
        $this->setPayment();
        $paySystems = $this->getPaySystems();
        $this->setProperties();
        $properties = $this->getProperties();
        $orderFields = $this->getFields();

        $data = [
            'LOCATION' => $location,
            'PERSON_TYPES' => $personTypes,
            'PROPERTIES' => $properties,
            'DELIVERIES' => $deliveries,
            'PAY_SYSTEMS' => $paySystems,
            'PRODUCTS_LIST' => $productsList,
            'ORDER' => $orderFields,
        ];

        $this->result->setData($data);

        return $this->result;
    }

    public function saveOrder(): Result
    {
        $this->setPersonType();
        $this->setBasket();
        $this->setLocation(false);
        $this->setShipment();
        $this->setPayment();
        $this->setProperties(false);

        $this->validatePersonType();
        $this->validateShipment();
        $this->validatePayment();
        $this->validateProperties();

        if ($this->result->isSuccess() && !$this->order->getUserId()) {
            $this->setUserId();
        }

        if ($this->result->isSuccess()) {
            $result = $this->order->save();
            $this->result->mergeResult($result);
        }

        return $this->result;
    }

    protected function setPersonType(): void
    {
        if ($this->data['PERSON_TYPE_ID']) {
            $personTypeId = $this->data['PERSON_TYPE_ID'];
        } else {
            $personType = \Bitrix\Sale\PersonType::getList([
                'order' => ['SORT' => 'ASC'],
                'filter' => ['ACTIVE' => 'Y'],
            ])->fetch();
            $personTypeId = $personType['ID'];
        }

        if ($personTypeId > 0) {
            $this->order->setPersonTypeId($personTypeId);
        }
    }

    protected function getPersonTypes(): array
    {
        $orderPersonTypeId = $this->order->getPersonTypeId();
        $personTypes = [];

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

    protected function setBasket(): void
    {
        $basket = $this->basketHandler::getBasket(Fuser::getId());
        $this->order->setBasket($basket);
    }

    protected function getProductsList(): array
    {
        /** @var Basket $basket */

        $productsList = [];

        if ($basket = $this->order->getBasket()) {
            $productsList = $basket->getItemsList();
        }

        return $productsList;
    }

    protected function setLocation($useDefaults = true): void
    {
        /**
         * @var array $restrictedProperties
         * @var \Bitrix\Sale\PropertyValue $restrictedProperty
         */

        $restrictedProperties = $this->order->getRestrictedProperties();

        foreach ($restrictedProperties as $restrictedProperty) {
            if ($restrictedProperty->getType() === 'LOCATION') {
                $property = $restrictedProperty->getProperty();

                if ($this->data[$property['CODE']]) {
                    $propertyValue = $this->data[$property['CODE']];
                } elseif ($property['DEFAULT_VALUE'] && $useDefaults) {
                    $propertyValue = $property['DEFAULT_VALUE'];
                } else {
                    $propertyValue = '';
                }

                $restrictedProperty->setValue($propertyValue);
                break;
            }
        }
    }

    protected function getLocation(): array
    {
        /**
         * @var array $restrictedProperties
         * @var \Bitrix\Sale\PropertyValue $restrictedProperty
         */

        //todo ->getDeliveryLocation();
        $property = [];
        $restrictedProperties = $this->order->getRestrictedProperties();

        foreach ($restrictedProperties as $restrictedProperty) {
            if ($restrictedProperty->getType() === 'LOCATION') {
                $property = $restrictedProperty->getProperty();
                $property['VALUE'] = $restrictedProperty->getValue();
                break;
            }
        }

        return $property;
    }

    protected function setShipment(): void
    {
        /**
         * @var \Bitrix\Sale\ShipmentCollection $shipmentCollection
         * @var \WC\Sale\Shipment $shipment
         * @var \WC\Sale\Basket $basket
         * @var array $restrictedDeliveries
         * @var array $restrictedDelivery
         */

        $shipmentCollection = $this->order->getShipmentCollection();
        $shipment = $shipmentCollection->createItem();
        if ($basket = $this->order->getBasket()) {
            $shipment->fill($basket);
        }
        $restrictedDeliveries = \Bitrix\Sale\Delivery\Services\Manager::getRestrictedList(
            $shipment,
            \Bitrix\Sale\Delivery\Restrictions\Manager::MODE_CLIENT
        );
        $deliveryId = array_keys($restrictedDeliveries)[0];

        foreach ($restrictedDeliveries as $restrictedDelivery) {
            if ($this->data['DELIVERY_ID'] && $this->data['DELIVERY_ID'] == $restrictedDelivery['ID']) {
                $deliveryId = $restrictedDelivery['ID'];
                break;
            }
        }

        if ($deliveryId > 0 && $delivery = Delivery\Services\Manager::getObjectById($deliveryId)) {
            $shipment->setDeliveryService($delivery);
            $shipmentCollection->calculateDelivery();
        }
    }

    protected function getDeliveries(): array
    {
        /**
         * @var \Bitrix\Sale\ShipmentCollection $shipmentCollection
         * @var \WC\Sale\Shipment $shipment
         * @var array $restrictedDeliveries
         * @var array $restrictedDelivery
         */

        $deliveries = [];
        $shipmentCollection = $this->order->getShipmentCollection();
        if ($shipment = $shipmentCollection->getItemByIndex(1)) {
            $deliveryId = $shipment->getDeliveryId();
            $restrictedDeliveries = \Bitrix\Sale\Delivery\Services\Manager::getRestrictedList(
                $shipment,
                \Bitrix\Sale\Delivery\Restrictions\Manager::MODE_CLIENT
            );
        }

        foreach ($restrictedDeliveries as $restrictedDelivery) {
            $delivery = Delivery\Services\Manager::getById($restrictedDelivery['ID']);

            if ($deliveryId == $delivery['ID']) {
                $delivery['CHECKED'] = true;
            }

            $deliveries[] = $delivery;
        }

        return $deliveries;
    }

    protected function setPayment(): void
    {
        /**
         * @var \Bitrix\Sale\PaymentCollection $paymentCollection
         * @var \Bitrix\Sale\Payment $payment
         * @var array $restrictedPaySystems
         */

        $paymentCollection = $this->order->getPaymentCollection();
        $payment = $paymentCollection->createItem();
        $payment->setFields([
            'SUM' => $this->order->getPrice(),
            'CURRENCY' => $this->order->getCurrency(),
        ]);
        $restrictedPaySystems = \Bitrix\Sale\PaySystem\Manager::getListWithRestrictions($payment);
        $paySystemId = array_keys($restrictedPaySystems)[0];

        foreach ($restrictedPaySystems as $restrictedPaySystem) {
            if ($this->data['PAY_SYSTEM_ID'] && $this->data['PAY_SYSTEM_ID'] == $restrictedPaySystem['ID']) {
                $paySystemId = $restrictedPaySystem['ID'];
                break;
            }
        }

        if ($paySystemId > 0 && $paySystem = \Bitrix\Sale\PaySystem\Manager::getObjectById($paySystemId)) {
            $payment->setPaySystemService($paySystem);
        }
    }

    protected function getPaySystems(): array
    {
        /**
         * @var \Bitrix\Sale\PaymentCollection $paymentCollection
         * @var \Bitrix\Sale\Payment $payment
         * @var array $restrictedPaySystems
         */

        $paySystems = [];
        $paymentCollection = $this->order->getPaymentCollection();
        if ($payment = $paymentCollection->getItemByIndex(0)) {
            $paySystemId = $payment->getPaymentSystemId();
            $restrictedPaySystems = \Bitrix\Sale\PaySystem\Manager::getListWithRestrictions($payment);
        }

        foreach ($restrictedPaySystems as $restrictedPaySystem) {
            $paySystem = \Bitrix\Sale\PaySystem\Manager::getById($restrictedPaySystem['ID']);

            if ($paySystemId == $paySystem['ID']) {
                $paySystem['CHECKED'] = true;
            }

            $paySystems[] = $paySystem;
        }

        return $paySystems;
    }

    protected function setProperties($useDefaults = true): void
    {
        /**
         * @var array $restrictedProperties
         * @var \Bitrix\Sale\PropertyValue $restrictedProperty
         */

        $restrictedProperties = $this->order->getRestrictedProperties();

        foreach ($restrictedProperties as $restrictedProperty) {
            if ($restrictedProperty->isUtil() || $restrictedProperty->getType() === 'LOCATION') {
                continue;
            }

            $property = $restrictedProperty->getProperty();

            if ($this->data[$property['CODE']]) {
                $propertyValue = $this->data[$property['CODE']];
            } elseif ($property['DEFAULT_VALUE'] && $useDefaults) {
                $propertyValue = $property['DEFAULT_VALUE'];
            } else {
                $propertyValue = $property['MULTIPLE'] === 'Y' ? [''] : '';
            }

            $restrictedProperty->setValue($propertyValue);
        }
    }

    protected function getProperties(): array
    {
        /**
         * @var array $restrictedProperties
         * @var \Bitrix\Sale\PropertyValue $restrictedProperty
         */

        $properties = [];
        $restrictedProperties = $this->order->getRestrictedProperties();

        foreach ($restrictedProperties as $restrictedProperty) {
            if ($restrictedProperty->isUtil() || $restrictedProperty->getType() === 'LOCATION') {
                continue;
            }

            $property = $restrictedProperty->getProperty();
            $property['VALUE'] = $restrictedProperty->getValue();
            $properties[] = $property;
        }

        return $properties;
    }

    protected function getFields(): array
    {
        /** @var Basket $basket */

        $orderFields = $this->order->getFieldValuesFormatted();
        $basketFields = [];

        if ($basket = $this->order->getBasket()) {
            $basketFields = $basket->getFieldValuesFormatted();
        }

        return array_merge($orderFields, $basketFields);
    }

    protected function validatePersonType(): void
    {
        if (!$this->order->getPersonTypeId()) {
            $this->result->addError('WC_SALE_PERSON_TYPE_ERROR');
        }
    }

    protected function validateShipment(): void
    {
        /**
         * @var \Bitrix\Sale\ShipmentCollection $shipmentCollection
         * @var \WC\Sale\Shipment $shipment
         */

        $shipmentCollection = $this->order->getShipmentCollection();

        if ($shipment = $shipmentCollection->getItemByIndex(1)) {
            $deliveryId = $shipment->getDeliveryId();
        }

        if (!$deliveryId) {
            $this->result->addError('WC_SALE_SHIPMENT_ERROR');
        }
    }

    protected function validatePayment(): void
    {
        /**
         * @var \Bitrix\Sale\PaymentCollection $paymentCollection
         * @var \Bitrix\Sale\Payment $payment
         */

        $paymentCollection = $this->order->getPaymentCollection();

        if ($payment = $paymentCollection->getItemByIndex(0)) {
            $paymentSystemId = $payment->getPaymentSystemId();
        }

        if (!$paymentSystemId) {
            $this->result->addError('WC_SALE_PAYMENT_ERROR');
        }
    }

    protected function validateProperties(): void
    {
        /**
         * @var \Bitrix\Main\Result $result
         * @var \Bitrix\Sale\PropertyValue $property
         */

        foreach ($this->order->getPropertyCollection() as $property) {
            if ($property->isUtil()) {
                continue;
            }

            $result = $property->checkRequiredValue($property->getPropertyId(), $property->getValue());

            if ($result->isSuccess()) {
                $result = $property->verify();
            }

            if (!$result->isSuccess()) {
                $this->result->addErrors($result->getErrors());
            }
        }
    }

    protected function setUserId(): void
    {
        global $USER;

        if ($USER->IsAuthorized()) {
            $userId = $USER->GetID();
        } else {
            $user = \WC\Sale\Handlers\OrderUser::autoRegister([$this->order->getPropertyCollection()]);
            if (!$userId = $user->GetId()) {
                $this->result->addError($user->LAST_ERROR);
            }
        }

        if ($userId > 0) {
            $this->order->setFieldNoDemand('USER_ID', $userId);
        }
    }

    protected function setFields(): void
    {

    }
}
