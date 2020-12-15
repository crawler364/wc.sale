<?php

use Bitrix\Main\Loader;

Loader::includeModule('sale');
Loader::includeModule('catalog');
Loader::includeModule('wc.main');

$registry = Bitrix\Sale\Registry::getInstance(Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);
$registry->set(Bitrix\Sale\Registry::ENTITY_BASKET, WC\Sale\Basket::class);
$registry->set(Bitrix\Sale\Registry::ENTITY_BASKET_ITEM, WC\Sale\BasketItem::class);