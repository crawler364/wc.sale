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
                $this->updateBasketItemFields();
            } else {
                $this->addBasketItemFields();
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

    protected function addBasketItemFields(): void
    {
        $this->basketItem->setField('PRODUCT_PROVIDER_CLASS', $this->productProvider);
        $this->basketItem->setField('QUANTITY', $this->quantity);
    }

    protected function updateBasketItemFields(): void
    {
        $this->basketItem->setField('QUANTITY', $this->quantity);

        Loader::includeModule('iblock');
        $iBlockId = \CIBlockElement::GetIBlockByID($this->basketItem->getProductId());
        $iBlockClass = \Bitrix\Iblock\Iblock::wakeUp($iBlockId)->getEntityDataClass();

        unset($this->parameters['PROPERTIES'][1]);

        /** @var \Bitrix\Iblock\Elements\EO_ElementCatalogoffers $element */
        $element = $iBlockClass::getByPrimary($this->basketItem->getProductId(), [
            'select' => $this->parameters['PROPERTIES'],
        ])->fetchObject();


        /** @var \Bitrix\Iblock\Elements\EO_IblockProperty20 $propertyValue */
        foreach ($element->collectValues() as $propertyValue) {
            if ($propertyValue instanceof \Bitrix\Iblock\Elements\EO_IblockProperty20) {
                $property = \Bitrix\Iblock\PropertyTable::getById($propertyValue->getIblockPropertyId())->fetchObject();
                $this->basketItem->setProperty($property->getName(), $property->getCode(), $propertyValue->getValue());
            }

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
