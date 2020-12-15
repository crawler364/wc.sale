<?php

use Bitrix\Main\Loader;

Loader::includeModule('sale');
Loader::includeModule('catalog');
Loader::includeModule('wc.main');

WC\Sale\Tools::setRegistry(WC\Sale\Basket::class, 'ENTITY_BASKET');
WC\Sale\Tools::setRegistry(WC\Sale\BasketItem::class, 'ENTITY_BASKET_ITEM');