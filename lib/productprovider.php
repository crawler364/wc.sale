<?php


namespace WC\Sale;


class ProductProvider implements \IBXSaleProductProvider
{
    public static function GetProductData($arFields)
    {
        $price = []; // getProductPrice();

        return [
            'AVAILABLE_QUANTITY' => $arFields['QUANTITY'],
            'QUANTITY' => $arFields['QUANTITY'],
            'CUSTOM_PRICE' => 'N', // Указывает на то могут ли, например, скидки изменять стоимость позиции или нет.
            'PRICE' => $price['PRICE_RESULT'], // PRICE Стоимость единицы товара (обязательное поле);
            'BASE_PRICE' => $price['PRICE'], // BASE_PRICE Базовая цена без скидок и тп?
            'DISCOUNT_PRICE' => $price['DISCOUNT'], // DISCOUNT_PRICE Величина скидки в валюте, а не цена со скидкой
            'DISCOUNT_VALUE' => $price['DISCOUNT_PERCENT'], // DISCOUNT_VALUE размер скидки (в процентах);
            'PRICE_TYPE_ID' => $price['CATALOG_GROUP_ID'],
            'PRODUCT_PRICE_ID' => $price['ID'],
            'CURRENCY' => $price['CURRENCY'],
            'VAT_RATE' => $price['VAT_RATE_MULTIPLY'],
            'VAT_INCLUDED' => $price['VAT_INCLUDED'],
        ];
    }

    public static function OrderProduct($arFields)
    {
        // TODO: Implement OrderProduct() method.
    }

    public static function CancelProduct($arFields)
    {
        // TODO: Implement CancelProduct() method.
    }

    public static function DeliverProduct($arFields)
    {
        // TODO: Implement DeliverProduct() method.
    }

    public static function ViewProduct($arFields)
    {
        // TODO: Implement ViewProduct() method.
    }

    public static function RecurringOrderProduct($arFields)
    {
        // TODO: Implement RecurringOrderProduct() method.
    }

    public static function GetStoresCount($arParams = [])
    {
        // TODO: Implement GetStoresCount() method.
    }

    public static function GetProductStores($arFields)
    {
        // TODO: Implement GetProductStores() method.
    }

    public static function ReserveProduct($arFields)
    {
        // TODO: Implement ReserveProduct() method.
    }

    public static function CheckProductBarcode($arFields)
    {
        // TODO: Implement CheckProductBarcode() method.
    }

    public static function DeductProduct($arFields)
    {
        // TODO: Implement DeductProduct() method.
    }
}