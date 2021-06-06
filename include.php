<?php

use Bitrix\Main\Loader;
use Bitrix\Main\IO\Directory;
use WC\Core\Helpers\Sale;
use WC\Sale\BasketPropertiesCollection;
use WC\Sale\Basket;
use WC\Sale\BasketItem;
use WC\Sale\Order;
use WC\Sale\Shipment;

Loader::registerAutoLoadClasses('wc.sale', [
    BasketPropertiesCollection::class => '/lib/basketproperties.php',
]);

if (Loader::includeModule('wc.core')) {
    Loader::includeModule('sale');
    Sale::setRegistry(Basket::class, 'ENTITY_BASKET');
    Sale::setRegistry(BasketItem::class, 'ENTITY_BASKET_ITEM');
    Sale::setRegistry(Order::class, 'ENTITY_ORDER');
    Sale::setRegistry(BasketPropertiesCollection::class, 'ENTITY_BASKET_PROPERTIES_COLLECTION');
    Sale::setRegistry(Shipment::class, 'ENTITY_SHIPMENT');
}

$kernelDir = Directory::isDirectoryExists($_SERVER['DOCUMENT_ROOT'] . '/local') ? '/local' : '/bitrix';

$arJsConfig = [
    'wc.sale.basket' => [
        'js' => "$kernelDir/js/wc/sale/basket.js",
    ],
    'wc.sale.order' => [
        'js' => "$kernelDir/js/wc/sale/order.js",
    ],
];

foreach ($arJsConfig as $ext => $arExt) {
    \CJSCore::RegisterExt($ext, $arExt);
}
