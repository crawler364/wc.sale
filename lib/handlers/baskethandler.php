<?php


namespace WC\Sale\Handlers;


use Bitrix\Catalog\ProductTable;
use Bitrix\Main\ArgumentTypeException;
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

    public function __construct($object)
    {
        $this->result = new Result();
        Loc::loadMessages(__FILE__);

        if ($object instanceof BasketItem) {
            $this->basketItem = $object;
            $this->basket = $object->getBasket();
        } elseif ($object instanceof Basket) {
            $this->basket = $object;
        } else {
            throw new ArgumentTypeException($object);
        }
    }

    public function processBasketItem($action, $quantity = null): Result
    {
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

        $fields = $this->basketItem->prepareBasketItemFields();
        $this->basketItem->setFields($fields);

        $this->basketItem->setNotes('PRICE_CODE');

        $this->basketItem->setPropertyArticle();
    }

    protected function updateBasketItemFields(): void
    {
        $this->basketItem->setField('QUANTITY', $this->quantity);
    }

    public static function getBasketItem($productId, Basket $basket = null): ?BasketItem
    {
        Loader::includeModule('catalog');

        $basket = $basket ?: self::getBasket();
        if (!$basketItem = $basket->getItemBy(['PRODUCT_ID' => $productId])) {
            if (ProductTable::getById($productId)->fetch()) {
                $basketItem = $basket->createItem('catalog', $productId);
            }
        }

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
