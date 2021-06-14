<?php


namespace WC\Sale\Handlers\Internals;


use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\Fuser;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Shipment;
use WC\Core\Bitrix\Main\Result;
use WC\Sale\Basket;
use WC\Sale\Handlers\Basket as BasketHandler;
use WC\Sale\Handlers\OrderUser;
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
    /** @var OrderUser */
    protected $orderUser = OrderUser::class;
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

    public function refreshOrder(): Result
    {
        $this->setPersonType();
        $personTypes = $this->getPersonTypes();
        $this->setBasket();
        $basketList = $this->getBasketList();
        $basketFields = $this->getBasketFields();
        $this->setLocation();
        $location = $this->getLocation();
        $this->setShipment();
        $deliveries = $this->getDeliveries();
        $this->setPayment();
        $paySystems = $this->getPaySystems();
        $this->setProperties();
        $properties = $this->getProperties();
        $fields = $this->getFields();

        $data = [
            'PERSON_TYPES' => $personTypes,
            'LOCATION' => $location,
            'BASKET_LIST' => $basketList,
            'BASKET_FIELDS' => $basketFields,
            'DELIVERIES' => $deliveries,
            'PAY_SYSTEMS' => $paySystems,
            'PROPERTIES' => $properties,
            'FIELDS' => $fields,
        ];

        $this->result->setData($data);

        return $this->result;
    }

    public function getOrder(): Result
    {
        $basketList = $this->getBasketList();
        $basketFields = $this->getBasketFields();
        $shipment = $this->getShipment();
        $payment = $this->getPayment();
        $properties = $this->getProperties();
        $fields = $this->getFields();

        $data = [
            'BASKET_LIST' => $basketList,
            'BASKET_FIELDS' => $basketFields,
            'SHIPMENT' => $shipment,
            'PAYMENT' => $payment,
            'PROPERTIES' => $properties,
            'FIELDS' => $fields,
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

            if ($result->isSuccess() && $orderId = $result->getId()) {
                $this->result->setData(['ORDER_ID' => $orderId]);
            }
        }

        return $this->result;
    }

    public static function loadOrder(array $filter): Result
    {
        $result = new Result();

        $parameters = [
            'filter' => $filter,
            'select' => '*',
        ];

        if ($order = Order::loadByFilter($parameters)[0]) {
            $result->setData(['ORDER' => $order]);
        } else {
            $result->addError('WC_SALE_ORDER_NOT_FOUND');
        }

        return $result;
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
        $personTypes = [];
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

    protected function setBasket(): void
    {
        $basket = $this->basketHandler::getBasket(Fuser::getId());
        $this->order->setBasket($basket);
    }

    protected function getBasketList(): array
    {
        /** @var Basket $basket */

        $basketList = [];

        if ($basket = $this->order->getBasket()) {
            $basketList = $basket->getItemsList();
        }

        return $basketList;
    }

    protected function getBasketFields(): array
    {
        /** @var Basket $basket */

        $basketFields = [];

        if ($basket = $this->order->getBasket()) {
            $basketFields = $basket->getFieldValuesFormatted();
        }

        return $basketFields;

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
        $location = [];
        $propertyCollection = $this->order->getPropertyCollection();
        $arPropertyCollection = $propertyCollection->getArray();

        foreach ($arPropertyCollection['properties'] as $property) {
            if ($property['TYPE'] === 'LOCATION') {
                $property['VALUE'] = $property['VALUE'][0];
                $location = $property;
                break;
            }
        }

        return $location;
    }

    protected function setShipment(): void
    {
        /**
         * @var \Bitrix\Sale\ShipmentCollection $shipmentCollection
         * @var \WC\Sale\Shipment $shipment
         * @var Basket $basket
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
        $shipment = $shipmentCollection->getItemByIndex(1);

        if ($shipment instanceof Shipment) {
            $deliveryId = $shipment->getDeliveryId();
            $restrictedDeliveries = \Bitrix\Sale\Delivery\Services\Manager::getRestrictedList(
                $shipment,
                \Bitrix\Sale\Delivery\Restrictions\Manager::MODE_CLIENT
            );

            foreach ($restrictedDeliveries as $restrictedDelivery) {
                $delivery = Delivery\Services\Manager::getById($restrictedDelivery['ID']);

                if ($deliveryId == $delivery['ID']) {
                    $delivery['CHECKED'] = true;
                }

                $deliveries[] = $delivery;
            }
        }

        return $deliveries;
    }

    protected function setPayment(): void
    {
        /**
         * @var \Bitrix\Sale\PaymentCollection $paymentCollection
         * @var Payment $payment
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
         * @var Payment $payment
         * @var array $restrictedPaySystems
         */

        $paySystems = [];
        $paymentCollection = $this->order->getPaymentCollection();
        $payment = $paymentCollection->getItemByIndex(0);

        if ($payment instanceof Payment) {
            $paySystemId = $payment->getPaymentSystemId();
            $restrictedPaySystems = \Bitrix\Sale\PaySystem\Manager::getListWithRestrictions($payment);

            foreach ($restrictedPaySystems as $restrictedPaySystem) {
                $paySystem = \Bitrix\Sale\PaySystem\Manager::getById($restrictedPaySystem['ID']);

                if ($paySystemId == $paySystem['ID']) {
                    $paySystem['CHECKED'] = true;
                }

                $paySystems[] = $paySystem;
            }
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
        $properties = [];
        $propertyCollection = $this->order->getPropertyCollection();
        $arPropertyCollection = $propertyCollection->getArray();

        foreach ($arPropertyCollection['properties'] as $property) {
            if ($property['UTIL'] === 'Y' || $property['TYPE'] === 'LOCATION') {
                continue;
            }

            if ($property['MULTIPLE'] === 'N') {
                $property['VALUE'] = $property['VALUE'][0];
            }

            $properties[] = $property;
        }

        return $properties;
    }

    protected function getFields(): array
    {
        return $this->order->getFieldValuesFormatted();
    }

    protected function getShipment(): array
    {
        $arShipment = [];
        $shipmentCollection = $this->order->getShipmentCollection();
        $shipment = $shipmentCollection->getItemByIndex(1);

        if ($shipment instanceof Shipment && $delivery = $shipment->getDelivery()) {
            $arShipment = $shipment->getFieldValues();
            $arShipment['LOGO'] = $delivery->getLogotipPath();
        }

        return $arShipment;
    }

    protected function getPayment(): array
    {
        /** @var \Bitrix\Sale\PaySystem\ServiceResult $serviceResult */

        $arPayment = [];
        $paymentCollection = $this->order->getPaymentCollection();
        $payment = $paymentCollection->getItemByIndex(0);

        if ($payment instanceof Payment && $paySystem = $payment->getPaySystem()) {
            $serviceResult = $paySystem->initiatePay($payment, null, \Bitrix\Sale\PaySystem\BaseServiceHandler::STRING);

            if ($serviceResult->isSuccess()) {
                $arPayment = $payment->getFieldValues();
                $arPaySystem = $paySystem->getFieldsValues();
                $arPayment['BUFFERED_OUTPUT'] = $serviceResult->getTemplate();
                $arPayment = array_merge($arPayment, $arPaySystem);
            } else {
                $arPayment["ERROR"] = $serviceResult->getErrorMessages();
            }
        }

        return $arPayment;
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
         * @var Payment $payment
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
            $r = $this->orderUser::autoRegister([$this->order->getPropertyCollection()]);

            if ((int)$r > 0) {
                $userId = $r;
            } else {
                $this->result->addError($r->LAST_ERROR);
            }
        }

        if ((int)$userId > 0) {
            $this->order->setFieldNoDemand('USER_ID', $userId);
        }
    }

    protected function setFields(): void
    {

    }
}
