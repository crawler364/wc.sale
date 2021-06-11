<?php


namespace WC\Sale\Components;


use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Sale\Fuser;
use WC\Core\Bitrix\Main\Result;
use WC\Sale\Handlers\Basket as BasketHandler;

Loc::loadMessages(__FILE__);

class BasketAjaxController extends Controller
{
    private $arParams;
    private $arResult;
    /** @var Basket */
    private $class;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this->class = '\\' . \CBitrixComponent::includeComponentClass('wc:basket');
        $this->class::checkModules(['wc.core', 'wc.sale']);
    }

    public function configureActions(): array
    {
        return [
            'process' => [
                'prefilters' => [], 'postfilters' => [],
            ],
        ];
    }

    protected function prepareParams(): bool
    {
        $this->arParams = $this->getUnsignedParameters();

        return true;
    }

    protected function processBeforeAction(Action $action): bool
    {
        $this->arResult = $this->request->toArray();

        return true;
    }

    public function processAction(): AjaxJson
    {
        /**
         * @var Result $result
         * @var BasketHandler $cBasketHandler
         * @var BasketHandler $basketHandler
         */

        $cBasketHandler = $this->class::getCBasketHandler($this->arParams);

        $basket = $cBasketHandler::getBasket(Fuser::getId());
        $basketItem = $basket->getItemBy(['PRODUCT_ID' => $this->arResult['product']['id']]) ??
            $cBasketHandler::createBasketItem($this->arResult['product']['id'], $basket);

        if ($basketItem) {
            $basketHandler = new $cBasketHandler($basket, $this->arParams);
            $basketHandler->processBasketItem($basketItem, [
                'ACTION' => $this->arResult['product']['action'],
                'QUANTITY' => $this->arResult['product']['quantity'],
            ]);
            $result = $basketHandler->saveBasket();
        } else {
            $result = new Result();
            $result->addError('WC_BASKET_UNDEFINED_PRODUCT');
        }

        return $result->prepareAjaxJson();
    }
}
