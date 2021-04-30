<?php


namespace WC\Sale\Handlers;


use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Fuser;
use WC\Core\Bitrix\Main\Result;
use WC\Core\Helpers\Main;
use WC\Sale\BasketItem;
use WC\Sale\Basket;

class BasketHandler
{
    /** @var BasketItem */
    protected $basketItem;
    /** @var Basket */
    protected $basket;
    /** @var CatalogProvider */
    protected $productProvider = CatalogProvider::class;
    private Result $result;
    private $quantity;
    private array $parameters;

    public function __construct(Basket $basket, array $parameters = [])
    {
        $this->result = new Result();
        Loc::loadMessages(__FILE__);

        $this->basket = $basket;
        $this->parameters = $parameters;
    }

    public function processBasketItem(BasketItem $basketItem, $action, $quantity = null): Result
    {
        $this->basketItem = $basketItem;

        if ($action !== 'set') {
            $quantity = $this->basketItem->mathQuantity($action);
        }

        $this->quantity = $this->basketItem->checkQuantity($quantity);

        if ($this->quantity > 0) {
            if ($this->basketItem->getId() > 0) {
                $this->updateBasketItem();
            } else {
                $this->addBasketItem();
            }
        } else {
            $this->basketItem->delete();
        }

        // todo $this->result += данные по доставке?

        return $this->result;
    }

    public function saveBasket(): Result
    {
        $r = $this->basket->save();

        $this->result->mergeResult($r);

        if ($this->result->isSuccess()) {
            $this->result->setData([
                'BASKET_ITEM' => $this->basketItem->getInfo(),
                'BASKET' => $this->basket->getData(),
            ]);
        }

        return $this->result;
    }

    protected function addBasketItem(): void
    {
        $this->basketItem->setField('PRODUCT_PROVIDER_CLASS', $this->productProvider);
        $this->basketItem->setField('QUANTITY', $this->quantity);
        $this->setBasketItemProperties();
    }

    protected function updateBasketItem(): void
    {
        $this->basketItem->setField('QUANTITY', $this->quantity);
    }

    protected function setBasketItemProperties(): void
    {
        Loader::includeModule('iblock');
        $element = ElementTable::getList([
            'select' => ['IBLOCK_ID', 'IBLOCK_VERSION' => 'IBLOCK.VERSION'],
            'filter' => ['=ID' => $this->basketItem->getProductId()],
            'cache' => ['ttl' => 604800],
        ])->fetch();

        if ($element['IBLOCK_VERSION'] === '2') {
            $propertyEntity = \WC\Core\ORM\IBlock\ElementPropertySTable::compileEntity($element['IBLOCK_ID']);
        }

        foreach ($this->parameters['PROPERTIES'] as $propertyCode) {
            if (!$property = PropertyTable::getList([
                'select' => ['ID', 'NAME', 'PROPERTY_TYPE'],
                'filter' => ['=IBLOCK_ID' => $element['IBLOCK_ID'], '=CODE' => $propertyCode],
            ])->fetch()) {
                continue;
            }

            if ($propertyEntity) {
                $propertyKey = "PROPERTY_{$property['ID']}";
                $select = [$propertyKey];

                if ($property['PROPERTY_TYPE'] === 'L') {
                    $propertyEnumKey = "PROPERTY_{$property['ID']}_ENUM";
                    $select[] = $propertyEnumKey;
                }

                if (!$obPropertyValue = $propertyEntity::getList([
                    'select' => $select,
                    'filter' => ['=IBLOCK_ELEMENT_ID' => $this->basketItem->getProductId()],
                ])->fetchObject()) {
                    continue;
                }

                if ($property['PROPERTY_TYPE'] === 'L' && $propertyEnum = $obPropertyValue->get($propertyEnumKey)) {
                    $value = $propertyEnum->getValue();
                } else {
                    $value = $obPropertyValue->get($propertyKey);
                }
            } else {
                if (!$obPropertyValue = \Bitrix\Iblock\ElementPropertyTable::getList([
                    'select' => ['VALUE', 'ENUM.VALUE'],
                    'filter' => [
                        '=IBLOCK_PROPERTY_ID' => $property['ID'],
                        '=IBLOCK_ELEMENT_ID' => $this->basketItem->getProductId(),
                    ],
                ])->fetchObject()) {
                    continue;
                }

                if ($property['PROPERTY_TYPE'] === 'L' && $propertyEnum = $obPropertyValue->getEnum()) {
                    $value = $propertyEnum->getValue();
                } else {
                    $value = $obPropertyValue->getValue();
                }
            }

            $this->basketItem->setProperty($property['NAME'], $propertyCode, $value);
        }
    }

    public static function getBasketItem($productId, Basket $basket = null): ?BasketItem
    {
        Loader::includeModule('catalog');

        $basket = $basket ?: self::getBasket();

        if (ProductTable::getById($productId)->fetch()) {
            $basketItem = $basket->createItem('catalog', $productId);
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $basketItem;
    }

    public static function getBasket(int $userId = null): Basket
    {
        if ($userId) {
            $fUserId = Fuser::getIdByUserId($userId);
        } else {
            $fUserId = Fuser::getId();
        }
        $siteId = Main::getSiteId();
        $basket = Basket::loadItemsForFUser($fUserId, $siteId);
        $order = OrderHandler::createOrder();
        $order->appendBasket($basket);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $basket;
    }
}
