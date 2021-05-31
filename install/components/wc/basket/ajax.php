<?php


namespace WC\Sale\Components;


use Bitrix\Main\Engine\Action;
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
    private $arParams;
    private $arResult;

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

    protected function prepareParams(): bool
    {
        $this->arParams = $this->getUnsignedParameters();

        return parent::prepareParams();
    }

    protected function processBeforeAction(Action $action): bool
    {
        $this->arResult = $this->request->toArray();

        return parent::processBeforeAction($action);
    }

    public function processAction(): AjaxJson
    {
        /**
         * @var Result $result
         * @var BasketHandler $cBasketHandler
         * @var BasketHandler $basketHandler
         */

        $cBasketHandler = $this->getCBasketHandler();

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

    private function checkModules(array $modules): void
    {
        foreach ($modules as $module) {
            if (!Loader::includeModule($module)) {
                throw new LoaderException(Loc::getMessage('WC_BASKET_MODULE_NOT_INCLUDED', ['#REPLACE#' => $module]));
            }
        }
    }

    private function getCBasketHandler(): string
    {
        if (class_exists($this->arParams['BASKET_HANDLER_CLASS'])) {
            $cBasketHandler = $this->arParams['BASKET_HANDLER_CLASS'];
        } elseif (class_exists(BasketHandler::class)) {
            $cBasketHandler = BasketHandler::class;
        } else {
            throw new SystemException(Loc::getMessage('WC_BASKET_HANDLER_CLASS_NOT_EXISTS'));
        }

        return $cBasketHandler;
    }
}
