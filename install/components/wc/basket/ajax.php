<?php


namespace WC\Sale\Components;


use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Loader;
use Bitrix\Sale\Fuser;
use WC\Core\Bitrix\Main\Result;
use WC\Sale\Handlers\Basket\Handler as BasketHandler;

Loc::loadMessages(__FILE__);

class BasketAjaxController extends Controller
{
    /** @var BasketHandler $cBasketHandler */
    private $cBasketHandler;
    private $arParams;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this->checkModules(['wc.core', 'wc.sale']);
    }

    public function configureActions(): array
    {
        return [
            'process' => [
                'prefilters' => [], 'postfilters' => [],
            ],
        ];
    }

    /**
     * @param array $product id, quantity, action
     * @param array $parameters component params
     * @return AjaxJson
     */
    public function processAction(array $product, array $parameters = []): AjaxJson
    {
        /** @var Result $result */

        $this->setArParams($parameters);
        $this->setCBasketHandler();

        $basket = $this->cBasketHandler::getBasket(Fuser::getId());
        $basketItem = $basket->getItemBy(['PRODUCT_ID' => $product['id']]) ??
            $this->cBasketHandler::createBasketItem($product['id'], $basket);

        if ($basketItem) {
            $basketHandler = new $this->cBasketHandler($basket, $this->arParams);
            $basketHandler->processBasketItem($basketItem, [
                'ACTION' => $product['action'],
                'QUANTITY' => $product['quantity'],
            ]);
            $result = $basketHandler->saveBasket();
        } else {
            $result = new Result();
            $result->addError('WC_BASKET_UNDEFINED_PRODUCT');
        }

        return $result->prepareAjaxJson();
    }

    private function checkModules(array $modules): void
    {
        foreach ($modules as $module) {
            if (!Loader::includeModule($module)) {
                throw new LoaderException(Loc::getMessage('WC_BASKET_MODULE_NOT_INCLUDED', ['#REPLACE#' => $module]));
            }
        }
    }

    private function setArParams($parameters): void
    {
        $this->arParams = $parameters;
    }

    private function setCBasketHandler(): void
    {
        if (class_exists($this->arParams['BASKET_HANDLER_CLASS'])) {
            $this->cBasketHandler = $this->arParams['BASKET_HANDLER_CLASS'];
        } elseif (class_exists(BasketHandler::class)) {
            $this->cBasketHandler = BasketHandler::class;
        } else {
            throw new SystemException(Loc::getMessage('WC_BASKET_HANDLER_CLASS_NOT_EXISTS'));
        }
    }
}
