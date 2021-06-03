<?php


namespace WC\Sale\Handlers\Order;


use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery;
use Bitrix\Main\Context;
use Bitrix\Sale\Fuser;
use WC\Core\Bitrix\Main\Result;
use WC\Sale\Handlers\Basket\Handler as BasketHandler;
use WC\Sale\Order;

Loc::loadMessages(__FILE__);

abstract class HandlerBase implements HandlerInterface
{
    /** @var Result */
    protected $result;
    /** @var Order */
    protected $order;
    /** @var BasketHandler */
    protected $basketHandler = BasketHandler::class;
    protected $usePropertiesDefaultValue = true;
    protected $orderData;

    public function __construct(Order $order, array $parameters = [])
    {
        $this->result = new Result();
        $this->order = $order;
        $this->parseParameters($parameters);
    }

    /**
     * @param int|null $userId
     * @return Order|\Bitrix\Sale\Order
     */
    public static function createOrder(int $userId = null)
    {
        if ($userId == null) {
            $fUserId = Fuser::getId();
            $userId = Fuser::getUserIdById($fUserId);
        }

        $siteId = \WC\Core\Helpers\Main::getSiteId();

        return Order::create($siteId, $userId);
    }

    public function processOrder(): Result
    {
        // todo $this->checkOrderData();

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

        $data = [
            'LOCATION' => $location,
            'PERSON_TYPES' => $personTypes,
            'PROPERTIES' => $properties,
            'DELIVERIES' => $deliveries,
            'PAY_SYSTEMS' => $paySystems,
            'PRODUCTS_LIST' => $productsList,
            'ORDER_INFO' => $this->order->getInfo(),
            'BASKET_INFO' => $this->order->getBasket()->getInfo(),
        ];

        $this->result->setData($data);

        return $this->result;
    }

    public function saveOrder(): Result
    {
        // todo $this->checkOrderData();

        $this->setPersonType();
        $this->setBasket();
        $this->setLocation();
        $this->setShipment();
        $this->setPayment();
        $this->setProperties();

        $result = $this->order->save();
        $this->result->mergeResult($result);

        return $this->result;
    }

    protected function parseParameters($parameters): void
    {
        $this->orderData = $parameters['ORDER_DATA'] ?? Context::getCurrent()->getRequest()->toArray();
        $this->usePropertiesDefaultValue = $parameters['USE_PROPERTIES_DEFAULT_VALUE'] !== false;
    }

    protected function setPersonType(): void
    {
        if ($this->orderData['PERSON_TYPE_ID']) {
            $personTypeId = $this->orderData['PERSON_TYPE_ID'];
        } else {
            $personType = \Bitrix\Sale\PersonType::getList([
                'order' => ['SORT' => 'ASC'],
                'filter' => ['ACTIVE' => 'Y'],
            ])->fetch();
            $personTypeId = $personType['ID'];
        }

        if ($personTypeId > 0) {
            $this->order->setPersonTypeId($personTypeId);
        } else {
            $this->result->addError('WC_SALE_PERSON_TYPE_ERROR');
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
        $productsList = [];

        if ($basket = $this->order->getBasket()) {
            $productsList = $basket->getItemsList();
        }

        return $productsList;
    }

    protected function setLocation(): void
    {
        /**
         * @var \Bitrix\Sale\PropertyValueCollection $propertyCollection
         * @var array $restrictedProperties
         * @var \Bitrix\Sale\PropertyValue $restrictedProperty
         */

        $propertyCollection = $this->order->getPropertyCollection();
        $restrictedProperties = $this->order->getRestrictedProperties($propertyCollection);

        foreach ($restrictedProperties as $restrictedProperty) {
            if ($restrictedProperty->getType() === 'LOCATION') {
                $property = $restrictedProperty->getProperty();

                if ($this->orderData[$property['CODE']]) {
                    $propertyValue = $this->orderData[$property['CODE']];
                } elseif ($property['DEFAULT_VALUE'] && $this->usePropertiesDefaultValue) {
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
         * @var \Bitrix\Sale\PropertyValueCollection $propertyCollection
         * @var array $restrictedProperties
         * @var \Bitrix\Sale\PropertyValue $restrictedProperty
         */

        //todo ->getDeliveryLocation();
        $propertyCollection = $this->order->getPropertyCollection();
        $restrictedProperties = $this->order->getRestrictedProperties($propertyCollection);
        $property = [];

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
         * @var \Bitrix\Sale\Shipment $shipment
         * @var \Bitrix\Sale\ShipmentItem $shipmentItem
         * @var array $restrictedDeliveries
         * @var array $restrictedDelivery
         */

        $shipmentCollection = $this->order->getShipmentCollection();
        $restrictedDeliveries = $this->order->getRestrictedDeliveries($shipmentCollection);

        foreach ($restrictedDeliveries as $key => $restrictedDelivery) {
            if ($key === 0) {
                $deliveryId = $restrictedDelivery['ID'];
            }

            if ($this->orderData['DELIVERY_ID'] && $this->orderData['DELIVERY_ID'] == $restrictedDelivery['ID']) {
                $deliveryId = $restrictedDelivery['ID'];
                break;
            }
        }

        if ($deliveryId > 0) {
            $delivery = Delivery\Services\Manager::getObjectById($deliveryId);
            $shipment = $shipmentCollection->createItem($delivery);
            $shipmentItemCollection = $shipment->getShipmentItemCollection();
            $basket = $this->order->getBasket();

            foreach ($basket as $basketItem) {
                if ($shipmentItem = $shipmentItemCollection->createItem($basketItem)) {
                    $shipmentItem->setQuantity($basketItem->getQuantity());
                }
            }
        } else {
            $this->result->addError('WC_SALE_SHIPMENT_ERROR');
        }
    }

    protected function getDeliveries(): array
    {
        /**
         * @var \Bitrix\Sale\ShipmentCollection $shipmentCollection
         * @var array $restrictedDeliveries
         * @var array $restrictedDelivery
         */

        $deliveries = [];
        $shipmentCollection = $this->order->getShipmentCollection();
        $restrictedDeliveries = $this->order->getRestrictedDeliveries($shipmentCollection);
        $deliveryId = $shipmentCollection->getItemByIndex(1)->getDeliveryId();

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
        $restrictedPaySystems = $this->order->getRestrictedPaySystems($paymentCollection);

        foreach ($restrictedPaySystems as $key => $restrictedPaySystem) {
            if ($key === 0) {
                $paySystemId = $restrictedPaySystem['ID'];
            }

            if ($this->orderData['PAY_SYSTEM_ID'] && $this->orderData['PAY_SYSTEM_ID'] == $restrictedPaySystem['ID']) {
                $paySystemId = $restrictedPaySystem['ID'];
                break;
            }
        }

        if ($paySystemId > 0) {
            $paySystem = \Bitrix\Sale\PaySystem\Manager::getObjectById($paySystemId);
            $payment = $paymentCollection->createItem($paySystem);
            $payment->setField('SUM', $this->order->getPrice());
            $payment->setField('CURRENCY', $this->order->getCurrency());
        } else {
            $this->result->addError('WC_SALE_PAYMENT_ERROR');
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
        $restrictedPaySystems = $this->order->getRestrictedPaySystems($paymentCollection);
        $paySystemId = $paymentCollection->getItemByIndex(0)->getPaymentSystemId();

        foreach ($restrictedPaySystems as $restrictedPaySystem) {
            $paySystem = \Bitrix\Sale\PaySystem\Manager::getById($restrictedPaySystem['ID']);

            if ($paySystemId == $paySystem['ID']) {
                $paySystem['CHECKED'] = true;
            }

            $paySystems[] = $paySystem;
        }

        return $paySystems;
    }

    protected function setProperties(): void
    {
        /**
         * @var \Bitrix\Sale\PropertyValueCollection $propertyCollection
         * @var array $restrictedProperties
         * @var \Bitrix\Sale\PropertyValue $restrictedProperty
         */

        $propertyCollection = $this->order->getPropertyCollection();
        $restrictedProperties = $this->order->getRestrictedProperties($propertyCollection);

        foreach ($restrictedProperties as $restrictedProperty) {
            if ($restrictedProperty->isUtil() || $restrictedProperty->getType() === 'LOCATION') {
                continue;
            }

            $property = $restrictedProperty->getProperty();

            if ($this->orderData[$property['CODE']]) {
                $propertyValue = $this->orderData[$property['CODE']];
            } elseif ($property['DEFAULT_VALUE'] && $this->usePropertiesDefaultValue) {
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
         * @var \Bitrix\Sale\PropertyValueCollection $propertyCollection
         * @var array $restrictedProperties
         * @var \Bitrix\Sale\PropertyValue $restrictedProperty
         */

        $propertyCollection = $this->order->getPropertyCollection();
        $restrictedProperties = $this->order->getRestrictedProperties($propertyCollection);
        $properties = [];

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

    protected function addOrder()
    {
        // todo
    }

    protected function updateOrder()
    {
        // todo
    }
}
