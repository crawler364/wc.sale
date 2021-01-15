<?php

use Bitrix\Main\Loader;

Loader::includeModule('sale');
Loader::includeModule('wc.main');

WC\Sale\Tools::setRegistry(WC\Sale\Basket::class, 'ENTITY_BASKET');
WC\Sale\Tools::setRegistry(WC\Sale\BasketItem::class, 'ENTITY_BASKET_ITEM');

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