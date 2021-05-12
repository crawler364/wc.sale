<?php


namespace WC\Sale\Components;


use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Loader;
use WC\Core\Bitrix\Main\Result;
use WC\Sale\Handlers\BasketHandler;

Loc::loadMessages(__FILE__);

class BasketAjaxController extends Controller
{
    /** @var BasketHandler */
    private $cBasketHandler = BasketHandler::class;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this->checkModules();
    }

    public function configureActions(): array
    {
        return [
            'process' => [
                'prefilters' => [], 'postfilters' => [],
            ],
        ];
    }

    public function processAction(array $product, string $basketAction = '', array $parameters = []): AjaxJson
    {
        $cBasketHandler = $parameters['CLASS_BASKET_HANDLER'] ?: $this->cBasketHandler;

        $basket = $cBasketHandler::getBasket();
        $basketItem = $basket->getItemBy(['PRODUCT_ID' => $product['id']]) ??
            $cBasketHandler::getBasketItem($product['id'], $basket);

        if ($basketItem) {
            $basketHandler = new $cBasketHandler($basket, $parameters);
            $basketHandler->processBasketItem($basketItem, $basketAction, $product['quantity']);
            $result = $basketHandler->saveBasket();
        } else {
            $result = new Result();
            $result->addError('WC_BASKET_UNDEFINED_PRODUCT');
        }

        return $result->prepareAjaxJson();
    }

    private function checkModules(): bool
    {
        if (!Loader::includeModule('wc.core')) {
            throw new LoaderException(Loc::getMessage('WC_BASKET_MODULE_NOT_INCLUDED', ['#REPLACE#' => 'wc.core']));
        }

        if (!Loader::includeModule('wc.sale')) {
            throw new LoaderException(Loc::getMessage('WC_BASKET_MODULE_NOT_INCLUDED', ['#REPLACE#' => 'wc.sale']));
        }

        return true;
    }
}
