<?php


namespace WC\Sale\Handlers;


use WC\Core\Bitrix\Main\Result;
use WC\Sale\Order;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery;

Loc::loadMessages(__FILE__);

class OrderHandler
{
    /** @var Result */
    protected $result;
    /** @var Order */
    protected $order;
    /** @var BasketHandler */
    protected $basketHandler = BasketHandler::class;
    private $usePropertiesDefaultValue = true;
    private $orderData;

    public function __construct(Order $order, array $parameters = [])
    {
        $this->result = new Result();
        $this->order = $order;
        $this->parseParameters($parameters);
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
        $basket = $this->basketHandler::getBasket($this->order->getUserId());
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
         * @var array $orderPropertyCollection
         * @var \Bitrix\Sale\PropertyValue $orderProperty
         */

        $orderPropertyCollection = $this->order->getRestrictedProperties();

        foreach ($orderPropertyCollection as $orderProperty) {
            if ($orderProperty->getType() === 'LOCATION') {
                $property = $orderProperty->getProperty();

                if ($this->orderData[$property['CODE']]) {
                    $propertyValue = $this->orderData[$property['CODE']];
                } elseif ($property['DEFAULT_VALUE'] && $this->usePropertiesDefaultValue) {
                    $propertyValue = $property['DEFAULT_VALUE'];
                } else {
                    $propertyValue = '';
                }

                $orderProperty->setValue($propertyValue);
                break;
            }
        }
    }

    protected function getLocation(): array
    {
        /**
         * @var array $orderProperties
         * @var \Bitrix\Sale\PropertyValue $orderProperty
         */

        //todo ->getDeliveryLocation();
        $orderProperties = $this->order->getRestrictedProperties();
        $property = [];

        foreach ($orderProperties as $orderProperty) {
            if ($orderProperty->getType() === 'LOCATION') {
                $property = $orderProperty->getProperty();
                $property['VALUE'] = $orderProperty->getValue();
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
        $restrictedDeliveries = $this->getRestrictedDeliveries($shipmentCollection);

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
        $restrictedDeliveries = $this->getRestrictedDeliveries($shipmentCollection);
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
        $restrictedPaySystems = $this->getRestrictedPaySystems($paymentCollection);

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
        $restrictedPaySystems = $this->getRestrictedPaySystems($paymentCollection);
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
         * @var array $orderPropertyCollection
         * @var \Bitrix\Sale\PropertyValue $orderProperty
         */

        $orderPropertyCollection = $this->order->getRestrictedProperties();

        foreach ($orderPropertyCollection as $orderProperty) {
            if ($orderProperty->isUtil() || $orderProperty->getType() === 'LOCATION') {
                continue;
            }

            $property = $orderProperty->getProperty();

            if ($this->orderData[$property['CODE']]) {
                $propertyValue = $this->orderData[$property['CODE']];
            } elseif ($property['DEFAULT_VALUE'] && $this->usePropertiesDefaultValue) {
                $propertyValue = $property['DEFAULT_VALUE'];
            } else {
                $propertyValue = $property['MULTIPLE'] === 'Y' ? [''] : '';
            }

            $orderProperty->setValue($propertyValue);
        }
    }

    protected function getProperties(): array
    {
        /**
         * @var array $orderProperties
         * @var \Bitrix\Sale\PropertyValue $orderProperty
         */

        $orderProperties = $this->order->getRestrictedProperties();
        $properties = [];

        foreach ($orderProperties as $orderProperty) {
            if ($orderProperty->isUtil() || $orderProperty->getType() === 'LOCATION') {
                continue;
            }

            $property = $orderProperty->getProperty();
            $property['VALUE'] = $orderProperty->getValue();
            $properties[] = $property;
        }

        return $properties;
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
            'INFO' => $this->order->getInfo(),
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

    protected function addOrder()
    {
        // todo
    }

    protected function updateOrder()
    {
        // todo
    }

    protected function getRestrictedDeliveries(\Bitrix\Sale\ShipmentCollection $shipmentCollection): array
    {
        $shipment = \Bitrix\Sale\Shipment::create($shipmentCollection);
        $restrictedDeliveries = Delivery\Services\Manager::getRestrictedList(
            $shipment,
            Delivery\Restrictions\Manager::MODE_CLIENT
        );

        return array_values($restrictedDeliveries);
    }

    protected function getRestrictedPaySystems(\Bitrix\Sale\PaymentCollection $paymentCollection): array
    {
        $payment = \Bitrix\Sale\Payment::create($paymentCollection);
        $restrictedPaySystems = \Bitrix\Sale\PaySystem\Manager::getListWithRestrictions(
            $payment
        );

        return array_values($restrictedPaySystems);
    }

    /**
     * @param int|null $userId
     * @return Order|\Bitrix\Sale\Order
     */
    public static function createOrder(int $userId = null)
    {
        if ($userId == null) {
            global $USER;
            $userId = $USER->GetID();
        }

        $siteId = \WC\Core\Helpers\Main::getSiteId();

        return Order::create($siteId, $userId);
    }

    final public function getFilesPropertiesFromRequest($request)
    {
    }
}
