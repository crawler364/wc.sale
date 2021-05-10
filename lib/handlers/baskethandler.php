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

Loc::loadMessages(__FILE__);

class BasketHandler
{
    protected $basket;
    protected $basketItem;
    protected $productProvider = CatalogProvider::class;
    private $result;
    private $quantity;
    private $parameters;

    public function __construct(Basket $basket, array $parameters = [])
    {
        $this->result = new Result();
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
        // 0.00035500526428222656 cache
        // 0.00242304801940917970 nocache
        $cache = \Bitrix\Main\Data\Cache::createInstance();
        $productId = $this->basketItem->getProductId();
        $element = \Bitrix\Iblock\ElementTable::getByPrimary($productId, [
            'select' => ['IBLOCK_ID'], 'cache' => ['ttl' => 604800],
        ])->fetchObject();
        $iBlockId = $element->getIblockId();


        foreach ($this->parameters['PROPERTIES'] as $propertyCode) {
            $cacheId = md5("$iBlockId.$productId.$propertyCode.");

            if ($cache->initCache(3600, $cacheId)) {
                $result = $cache->getVars();
            } elseif ($cache->startDataCache()) {
                $result = [];
                \CIBlockElement::GetPropertyValuesArray(
                    $result,
                    $iBlockId,
                    ['ID' => $productId],
                    $propertyFilter = ['CODE' => $propertyCode],
                    $options = []
                );

                $cache->endDataCache($result);
            }

            if ($property = $result[$productId][$propertyCode]) {
                $this->basketItem->setProperty($property['NAME'], $property['CODE'], $property['VALUE']);
            }
        }
    }

    protected function setBasketItemPropertiesD7(): void
    {
        // 0.001798868179321289 cache
        // 0.001931905746459961 nocache
        $element = ElementTable::getList([
            'select' => ['IBLOCK_ID', 'IBLOCK_VERSION' => 'IBLOCK.VERSION'],
            'filter' => ['=ID' => $this->basketItem->getProductId()],
            'cache' => ['ttl' => 604800, "cache_joins" => true],
        ])->fetch();

        if ($element['IBLOCK_VERSION'] === '2') {
            $propertyEntity = \WC\Core\ORM\IBlock\ElementPropertySTable::compileEntity($element['IBLOCK_ID']);
        }

        foreach ($this->parameters['PROPERTIES'] as $propertyCode) {
            if (!$property = PropertyTable::getList([
                'select' => ['ID', 'NAME', 'PROPERTY_TYPE'],
                'filter' => ['=IBLOCK_ID' => $element['IBLOCK_ID'], '=CODE' => $propertyCode],
                'cache' => ['ttl' => 86400],
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

                if (!$elementProperty = $propertyEntity::getList([
                    'select' => $select,
                    'filter' => ['=IBLOCK_ELEMENT_ID' => $this->basketItem->getProductId()],
                    'cache' => ['ttl' => 3600, "cache_joins" => true],
                ])->fetchObject()) {
                    continue;
                }

                if ($property['PROPERTY_TYPE'] === 'L' && $propertyEnum = $elementProperty->get($propertyEnumKey)) {
                    $value = $propertyEnum->getValue();
                } else {
                    $value = $elementProperty->get($propertyKey);
                }
            } else {
                if (!$elementProperty = \Bitrix\Iblock\ElementPropertyTable::getList([
                    'select' => ['VALUE', 'ENUM.VALUE'],
                    'filter' => [
                        '=IBLOCK_PROPERTY_ID' => $property['ID'],
                        '=IBLOCK_ELEMENT_ID' => $this->basketItem->getProductId(),
                    ],
                    'cache' => ['ttl' => 3600, "cache_joins" => true],
                ])->fetchObject()) {
                    continue;
                }

                if ($property['PROPERTY_TYPE'] === 'L' && $propertyEnum = $elementProperty->getEnum()) {
                    $value = $propertyEnum->getValue();
                } else {
                    $value = $elementProperty->getValue();
                }
            }

            $this->basketItem->setProperty($property['NAME'], $propertyCode, $value);
        }
    }

    /**
     * @param $productId
     * @param Basket|\Bitrix\Sale\BasketBase|null $basket
     * @return BasketItem|\Bitrix\Sale\BasketItemBase
     */
    public static function getBasketItem($productId, Basket $basket = null)
    {
        Loader::includeModule('catalog');

        $basket = $basket ?: self::getBasket();

        if (ProductTable::getById($productId)->fetch()) {
            $basketItem = $basket->createItem('catalog', $productId);
        }

        return $basketItem;
    }

    /**
     * @param int|null $userId
     * @return Basket|\Bitrix\Sale\BasketBase
     */
    public static function getBasket(int $userId = null)
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

        return $basket;
    }
}
