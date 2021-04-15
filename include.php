<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('wc.sale', [
    WC\Sale\BasketPropertiesCollection::class => '/lib/basketproperties.php',
]);

if (Loader::includeModule('wc.core')) {
    Loader::includeModule('sale');
    WC\Core\Helpers\Sale::setRegistry(WC\Sale\Basket::class, 'ENTITY_BASKET');
    WC\Core\Helpers\Sale::setRegistry(WC\Sale\BasketItem::class, 'ENTITY_BASKET_ITEM');
    WC\Core\Helpers\Sale::setRegistry(WC\Sale\Order::class, 'ENTITY_ORDER');
    WC\Core\Helpers\Sale::setRegistry(WC\Sale\BasketPropertiesCollection::class, 'ENTITY_BASKET_PROPERTIES_COLLECTION');
}

$kernelDir = Bitrix\Main\IO\Directory::isDirectoryExists($_SERVER['DOCUMENT_ROOT'] . '/local') ? '/local' : '/bitrix';

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
