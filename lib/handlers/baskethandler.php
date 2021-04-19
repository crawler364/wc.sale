<?php


namespace WC\Sale\Handlers;


use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\Product\CatalogProvider;
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
        /** @var \Bitrix\Iblock\Property|\Bitrix\Main\ORM\Objectify\EntityObject|null $property */
        /** @var  \Bitrix\Iblock\EO_ElementProperty|\Bitrix\Main\ORM\Objectify\EntityObject|null $value */

        Loader::includeModule('iblock');
        $iBlockId = \CIBlockElement::GetIBlockByID($this->basketItem->getProductId());

        foreach ($this->parameters['PROPERTIES'] as $propertyCode) {
            if (!$property = \Bitrix\Iblock\PropertyTable::getList([
                'filter' => ['IBLOCK_ID' => $iBlockId, 'CODE' => $propertyCode],
            ])->fetchObject()) {
                continue;
            }

            if (!$value = \Bitrix\Iblock\ElementPropertyTable::getList([
                'select' => ['VALUE', 'ENUM'],
                'filter' => [
                    'IBLOCK_PROPERTY_ID' => $property->getId(),
                    'IBLOCK_ELEMENT_ID' => $this->basketItem->getProductId()
                ],
            ])->fetchObject()) {
                continue;
            }

            $value = $property->getPropertyType() === 'L' ? $value->getEnum()->getValue() : $value->getValue();

            $this->basketItem->setProperty($property->getName(), $property->getCode(), $value);
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
