<?php

Bitrix\Main\Loader::includeModule('sale');

$registry = Bitrix\Sale\Registry::getInstance(Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);
$registry->set(Bitrix\Sale\Registry::ENTITY_BASKET, WC\Sale\Basket::class);
$registry->set(Bitrix\Sale\Registry::ENTITY_BASKET_ITEM, WC\Sale\BasketItem::class);

/*Bitrix\Main\Loader::registerAutoLoadClasses('wc.classes', [
    WC\Sale\BasketHandler::class => '/lib/sale/baskethandler.php',
    WC\Sale\ProductProvider::class => '/lib/sale/productprovider.php',
    WC\Sale\BasketItem::class => '/lib/sale/basketitem.php',
    WC\Sale\Basket::class => '/lib/sale/basket.php',
]);*/