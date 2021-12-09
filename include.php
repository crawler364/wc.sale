<?php

use Bitrix\Main\Loader;
use Bitrix\Main\IO\Directory;
use Bitrix\Sale\Registry as SaleRegistry;
use WC\Core\Helpers\Registry;
use WC\Sale\BasketPropertiesCollection;
use WC\Sale\Basket;
use WC\Sale\BasketItem;
use WC\Sale\Order;
use WC\Sale\Shipment;

Loader::registerAutoLoadClasses('wc.sale', [
    BasketPropertiesCollection::class => '/lib/basketproperties.php',
]);

//region Registry
if (Loader::includeModule('wc.core') && Loader::includeModule('sale')) {
    $registrySale = new Registry(SaleRegistry::class, SaleRegistry::REGISTRY_TYPE_ORDER);
    $registrySale->set(SaleRegistry::ENTITY_BASKET, Basket::class);
    $registrySale->set(SaleRegistry::ENTITY_BASKET_ITEM, BasketItem::class);
    $registrySale->set(SaleRegistry::ENTITY_ORDER, Order::class);
    $registrySale->set(SaleRegistry::ENTITY_BASKET_PROPERTIES_COLLECTION, BasketPropertiesCollection::class);
    $registrySale->set(SaleRegistry::ENTITY_SHIPMENT, Shipment::class);
}
//endregion

//region JS CORE
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
//endregion
