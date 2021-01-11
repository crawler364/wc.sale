<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main\Localization\Loc;
?>
<table id="wc-basket">
    <thead>
    <tr>
        <td><?= Loc::getMessage('DETAIL_PICTURE') ?></td>
        <td><?= Loc::getMessage('ARTICLE') ?></td>
        <td><?= Loc::getMessage('NAME') ?></td>
        <td><?= Loc::getMessage('PRICE') ?></td>
        <td></td>
        <td><?= Loc::getMessage('PRICE_SUM') ?></td>
    </tr>
    </thead>
    <? foreach ($arResult['ITEMS'] as $item) { ?>
        <tr data-basket-item-id="<?= $item['PRODUCT_ID'] ?>">
            <td>
                <img class="detail-picture" src="<?= $item['ELEMENT']['DETAIL_PICTURE'] ?>"
                     alt="<?= Loc::getMessage('NAME') ?>">
            </td>
            <td><?= $item['ELEMENT']['PROPERTY_ARTICLE_VALUE'] ?></td>
            <td><?= $item['NAME'] ?></td>
            <td>
                <? if ($item['DISCOUNT']) { ?>
                    <div class="line-through"><?= $item['PRICE_BASE_FORMATTED'] ?></div>
                <? } ?>
                <div data-basket-item-price><?= $item['PRICE_FORMATTED'] ?></div>
            </td>
            <td>
                <table>
                    <tr>
                        <td data-action-basket-item="minus">-</td>
                        <td>
                            <label>
                                <input type="text" data-action-basket-item="set" value="<?= $item['QUANTITY'] ?>">
                            </label>
                        </td>
                        <td data-action-basket-item="plus">+</td>
                        <td data-action-basket-item="delete">x</td>
                    </tr>
                </table>
            </td>
            <td>
                <? if ($item['DISCOUNT_SUM']) { ?>
                    <div class="line-through" data-basket-item-price-base-sum><?= $item['PRICE_BASE_SUM_FORMATTED'] ?></div>
                <? } ?>
                <div data-basket-item-price-sum><?= $item['PRICE_SUM_FORMATTED'] ?></div>
                <? if ($item['DISCOUNT_SUM']) { ?>
                    <div><?= Loc::getMessage('DISCOUNT') ?> <span data-basket-item-discount-sum><?= $item['DISCOUNT_SUM_FORMATTED'] ?></span></div>
                <? } ?>
            </td>
        </tr>
    <? } ?>
    <tr>
        <td colspan="4"></td>
        <td><?= Loc::getMessage('BASKET_WEIGHT') ?></td>
        <td data-basket-weight><?= $arResult['INFO']['WEIGHT_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td colspan="4"></td>
        <td><?= Loc::getMessage('BASKET_COUNT') ?></td>
        <td data-basket-count><?= $arResult['INFO']['COUNT'] ?></td>
    </tr>
    <tr>
        <td colspan="4"></td>
        <td><?= Loc::getMessage('BASKET_VAT') ?></td>
        <td data-basket-vat><?= $arResult['INFO']['VAT_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td colspan="4"></td>
        <td><?= Loc::getMessage('BASKET_PRICE_BASE') ?></td>
        <td data-basket-price-base><?= $arResult['INFO']['PRICE_BASE_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td colspan="4"></td>
        <td><?= Loc::getMessage('BASKET_DISCOUNT') ?></td>
        <td data-basket-discount><?= $arResult['INFO']['DISCOUNT_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td colspan="4"></td>
        <td><?= Loc::getMessage('BASKET_PRICE') ?></td>
        <td data-basket-price><?= $arResult['INFO']['PRICE_FORMATTED'] ?></td>
    </tr>
</table>

<script type="text/javascript">
    WCSaleBasket = new WCSaleBasket(<?=Bitrix\Main\Web\Json::encode([
        'basketHandlerClass' => $arParams['BASKET_HANDLER_CLASS'],
    ])?>);
</script>