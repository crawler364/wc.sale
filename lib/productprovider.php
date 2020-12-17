<?php


namespace WC\Sale;


use Bitrix\Main,
    Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc,
    Bitrix\Sale\DiscountCouponsManager,
    Bitrix\Currency,
    Bitrix\Catalog,
    Bitrix\Catalog\Product\Price,
    Bitrix\Iblock;

class ProductProvider implements \IBXSaleProductProvider
{
    const CACHE_PRODUCT = 'CATALOG_PRODUCT';

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

    public static function ReserveProduct($arParams)
    {
        global $APPLICATION;

        $arRes = array();
        $arFields = array();

        if ((int)$arParams["PRODUCT_ID"] <= 0)
        {
            $APPLICATION->ThrowException(Loc::getMessage("RSRV_INCORRECT_ID"), "NO_ORDER_ID");
            $arRes["RESULT"] = false;
            return $arRes;
        }

        $disableReservation = !static::isReservationEnabled();


        if ((string)$arParams["UNDO_RESERVATION"] != "Y")
            $arParams["UNDO_RESERVATION"] = "N";

        $arParams["QUANTITY_ADD"] = doubleval($arParams["QUANTITY_ADD"]);

        $rsProducts = \CCatalogProduct::GetList(
            array(),
            array('ID' => $arParams["PRODUCT_ID"]),
            false,
            false,
            array(
                'ID',
                'CAN_BUY_ZERO',
                'QUANTITY_TRACE',
                'QUANTITY',
                'WEIGHT',
                'WIDTH',
                'HEIGHT',
                'LENGTH',
                'BARCODE_MULTI',
                'TYPE',
                'QUANTITY_RESERVED'
            )
        );

        $arProduct = $rsProducts->Fetch();
        if (empty($arProduct))
        {
            $APPLICATION->ThrowException(Loc::getMessage("RSRV_ID_NOT_FOUND", array("#PRODUCT_ID#" => $arParams["PRODUCT_ID"])), "ID_NOT_FOUND");
            $arRes["RESULT"] = false;
            return $arRes;
        }

        if (
            ($arProduct['TYPE'] == Catalog\ProductTable::TYPE_SKU || $arProduct['TYPE'] == Catalog\ProductTable::TYPE_EMPTY_SKU)
            && (string)Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') != 'Y'
        )
        {
            $APPLICATION->ThrowException(Loc::getMessage("RSRV_SKU_FOUND", array("#PRODUCT_ID#" => $arParams["PRODUCT_ID"])), "SKU_FOUND");
            $arRes["RESULT"] = false;
            return $arRes;
        }


        if ($disableReservation)
        {
            $startReservedQuantity = 0;

            if ($arParams["UNDO_RESERVATION"] != "Y")
                $arFields = array("QUANTITY" => $arProduct["QUANTITY"] - $arParams["QUANTITY_ADD"]);
            else
                $arFields = array("QUANTITY" => $arProduct["QUANTITY"] + $arParams["QUANTITY_ADD"]);

            $arRes["RESULT"] = \CCatalogProduct::Update($arParams["PRODUCT_ID"], $arFields);

            if (self::isNeedClearPublicCache(
                $arProduct['QUANTITY'],
                $arFields['QUANTITY'],
                $arProduct['QUANTITY_TRACE'],
                $arProduct['CAN_BUY_ZERO']
            ))
            {
                $productInfo = array(
                    'CAN_BUY_ZERO' => $arProduct['CAN_BUY_ZERO'],
                    'QUANTITY_TRACE' => $arProduct['QUANTITY_TRACE'],
                    'OLD_QUANTITY' => $arProduct['QUANTITY'],
                    'QUANTITY' => $arFields['QUANTITY'],
                    'DELTA' => $arFields['QUANTITY'] - $arProduct['QUANTITY']
                );
                self::clearPublicCache($arProduct['ID'], $productInfo);
            }
        }
        else
        {
            if ($arProduct["QUANTITY_TRACE"] == "N" || (isset($arParams["ORDER_DEDUCTED"]) && $arParams["ORDER_DEDUCTED"] == "Y"))
            {
                $arRes["RESULT"] = true;
                $arFields["QUANTITY_RESERVED"] = 0;
                $startReservedQuantity = 0;
            }
            else
            {
                $startReservedQuantity = $arProduct["QUANTITY_RESERVED"];

                if ($arParams["UNDO_RESERVATION"] == "N")
                {
                    if ($arProduct["CAN_BUY_ZERO"] == "Y")
                    {
                        $arFields["QUANTITY_RESERVED"] = $arProduct["QUANTITY_RESERVED"] + $arParams["QUANTITY_ADD"];

                        if ($arProduct["QUANTITY"] >= $arParams["QUANTITY_ADD"])
                        {
                            $arFields["QUANTITY"] = $arProduct["QUANTITY"] - $arParams["QUANTITY_ADD"];
                        }
                        elseif ($arProduct["QUANTITY"] < $arParams["QUANTITY_ADD"])
                        {
                            //reserve value, quantity will be negative
                            $arFields["QUANTITY"] = $arProduct["QUANTITY"] - $arParams["QUANTITY_ADD"];
                        }

                        $arRes["RESULT"] = \CCatalogProduct::Update($arParams["PRODUCT_ID"], $arFields);
                    }
                    else //CAN_BUY_ZERO = N
                    {
                        if ($arProduct["QUANTITY"] >= $arParams["QUANTITY_ADD"])
                        {
                            $arFields["QUANTITY"] = $arProduct["QUANTITY"] - $arParams["QUANTITY_ADD"];
                            $arFields["QUANTITY_RESERVED"] = $arProduct["QUANTITY_RESERVED"] + $arParams["QUANTITY_ADD"];
                        }
                        elseif ($arProduct["QUANTITY"] < $arParams["QUANTITY_ADD"])
                        {
                            //reserve only possible value, quantity = 0

                            $arRes["QUANTITY_NOT_RESERVED"] = $arParams["QUANTITY_ADD"] - $arProduct["QUANTITY"];

                            $arFields["QUANTITY"] = 0;
                            $arFields["QUANTITY_RESERVED"] = $arProduct["QUANTITY_RESERVED"] + $arProduct["QUANTITY"];

                            $APPLICATION->ThrowException(Loc::getMessage("RSRV_QUANTITY_NOT_ENOUGH_ERROR", self::GetProductCatalogInfo($arParams["PRODUCT_ID"])), "ERROR_NOT_ENOUGH_QUANTITY");
                        }
                        if (self::isNeedClearPublicCache(
                            $arProduct['QUANTITY'],
                            $arFields['QUANTITY'],
                            $arProduct['QUANTITY_TRACE'],
                            $arProduct['CAN_BUY_ZERO']
                        ))
                        {
                            $productInfo = array(
                                'CAN_BUY_ZERO' => $arProduct['CAN_BUY_ZERO'],
                                'QUANTITY_TRACE' => $arProduct['QUANTITY_TRACE'],
                                'OLD_QUANTITY' => $arProduct['QUANTITY'],
                                'QUANTITY' => $arFields['QUANTITY'],
                                'DELTA' => $arFields['QUANTITY'] - $arProduct['QUANTITY']
                            );
                            self::clearPublicCache($arProduct['ID'], $productInfo);
                        }
                        $arRes["RESULT"] = \CCatalogProduct::Update($arParams["PRODUCT_ID"], $arFields);
                    }
                }
                else //undo reservation
                {
                    $arFields["QUANTITY"] = $arProduct["QUANTITY"] + $arParams["QUANTITY_ADD"];

                    $needReserved = $arProduct["QUANTITY_RESERVED"] - $arParams["QUANTITY_ADD"];
                    if ($arParams["QUANTITY_ADD"] > $arProduct["QUANTITY_RESERVED"])
                    {
                        $needReserved = $arProduct["QUANTITY_RESERVED"];
                    }

                    $arFields["QUANTITY_RESERVED"] = $needReserved;

                    $arRes["RESULT"] = \CCatalogProduct::Update($arParams["PRODUCT_ID"], $arFields);
                    if (self::isNeedClearPublicCache(
                        $arProduct['QUANTITY'],
                        $arFields['QUANTITY'],
                        $arProduct['QUANTITY_TRACE'],
                        $arProduct['CAN_BUY_ZERO']
                    ))
                    {
                        $productInfo = array(
                            'CAN_BUY_ZERO' => $arProduct['CAN_BUY_ZERO'],
                            'QUANTITY_TRACE' => $arProduct['QUANTITY_TRACE'],
                            'OLD_QUANTITY' => $arProduct['QUANTITY'],
                            'QUANTITY' => $arFields['QUANTITY'],
                            'DELTA' => $arFields['QUANTITY'] - $arProduct['QUANTITY']
                        );
                        self::clearPublicCache($arProduct['ID'], $productInfo);
                    }
                }
            } //quantity trace
        }

        if ($arRes["RESULT"])
        {

            $needReserved = $arFields["QUANTITY_RESERVED"] - $startReservedQuantity;
            if ($startReservedQuantity > $arFields["QUANTITY_RESERVED"])
            {
                $needReserved = $arFields["QUANTITY_RESERVED"];
            }

            $arRes["QUANTITY_RESERVED"] = $needReserved;
        }
        else
        {
            $APPLICATION->ThrowException(Loc::getMessage("RSRV_UNKNOWN_ERROR", self::GetProductCatalogInfo($arParams["PRODUCT_ID"])), "UNKNOWN_RESERVATION_ERROR");
        }

        static::clearHitCache(self::CACHE_PRODUCT);

        $arRes['CAN_RESERVE'] = ($disableReservation ? "N" : "Y");

        return ['RESULT'=>true, 'QUANTITY_RESERVED'=>0, 'CAN_RESERVE'=>'N'];
    }

    public static function CheckProductBarcode($arFields)
    {
        // TODO: Implement CheckProductBarcode() method.
    }

    public static function DeductProduct($arFields)
    {
        // TODO: Implement DeductProduct() method.
    }

    public static function isReservationEnabled()
    {
        return !((string)\Bitrix\Main\Config\Option::get("catalog", "enable_reservation") == "N"
            && (string)\Bitrix\Main\Config\Option::get("sale", "product_reserve_condition") != "S"
            && !\Bitrix\Catalog\Config\State::isUsedInventoryManagement()
        );
    }

    public static function clearHitCache($type = null)
    {
        if ($type === null)
            self::$hitCache = array();
        elseif (!empty(self::$hitCache[$type]))
            unset(self::$hitCache[$type]);
    }

    protected static function isNeedClearPublicCache($currentQuantity, $newQuantity, $quantityTrace, $canBuyZero, $ratio = 1)
    {
        if (!defined('BX_COMP_MANAGED_CACHE'))
            return false;
        if ($canBuyZero == 'Y' || $quantityTrace == 'N')
            return false;
        if ($currentQuantity * $newQuantity > 0)
            return false;
        return true;
    }

    protected static function clearPublicCache($productID, $productInfo = array())
    {
        $productID = (int)$productID;
        if ($productID <= 0)
            return;
        $iblockID = (int)(isset($productInfo['IBLOCK_ID']) ? $productInfo['IBLOCK_ID'] : \CIBlockElement::GetIBlockByID($productID));
        if ($iblockID <= 0)
            return;
        if (!isset(self::$clearAutoCache[$iblockID]))
        {
            \CIBlock::clearIblockTagCache($iblockID);
            self::$clearAutoCache[$iblockID] = true;
        }

        $productInfo['ID'] = $productID;
        $productInfo['ELEMENT_IBLOCK_ID'] = $iblockID;
        $productInfo['IBLOCK_ID'] = $iblockID;
        if (isset($productInfo['CAN_BUY_ZERO']))
            $productInfo['NEGATIVE_AMOUNT_TRACE'] = $productInfo['CAN_BUY_ZERO'];
        foreach (GetModuleEvents('catalog', 'OnProductQuantityTrace', true) as $arEvent)
            ExecuteModuleEventEx($arEvent, array($productID, $productInfo));
    }
    private static function GetProductCatalogInfo($productID)
    {
        $productID = (int)$productID;
        if ($productID <= 0)
            return array();


        if (!$arProduct = static::getHitCache('IBLOCK_ELEMENT', $productID))
        {
            $dbProduct = \CIBlockElement::GetList(array(), array("ID" => $productID), false, false, array('ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID'));
            if ($arProduct = $dbProduct->Fetch())
            {
                static::setHitCache('IBLOCK_ELEMENT', $productID, $arProduct);
            }
        }

        return array(
            "#PRODUCT_ID#" => $arProduct["ID"],
            "#PRODUCT_NAME#" => $arProduct["NAME"],
        );
    }
    public static function getHitCache($type, $key)
    {
        if (!empty(self::$hitCache[$type]) && !empty(self::$hitCache[$type][$key]))
            return self::$hitCache[$type][$key];

        return false;
    }
    public static function setHitCache($type, $key, $value)
    {
        if (empty(self::$hitCache[$type]))
            self::$hitCache[$type] = array();

        if (empty(self::$hitCache[$type][$key]))
            self::$hitCache[$type][$key] = array();

        self::$hitCache[$type][$key] = $value;
    }
}