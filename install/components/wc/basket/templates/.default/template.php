<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main\Localization\Loc;

?>
<table id="wc-basket-items-container">
    <thead>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_DETAIL_PICTURE') ?></td>
        <td><?= Loc::getMessage('WC_BASKET_ARTICLE') ?></td>
        <td><?= Loc::getMessage('WC_BASKET_NAME') ?></td>
        <td><?= Loc::getMessage('WC_BASKET_PRICE') ?></td>
        <td></td>
        <td><?= Loc::getMessage('WC_BASKET_PRICE_SUM') ?></td>
    </tr>
    </thead>
    <? foreach ($arResult['ITEMS'] as $item) { ?>
        <tr data-basket-item-id="<?= $item['PRODUCT_ID'] ?>">
            <td>
                <img class="detail-picture" src="<?= $item['ELEMENT']['DETAIL_PICTURE'] ?>"
                     alt="<?= Loc::getMessage('WC_BASKET_NAME') ?>">
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
                    <div class="line-through"
                         data-basket-item-price-base-sum><?= $item['PRICE_BASE_SUM_FORMATTED'] ?></div>
                <? } ?>
                <div data-basket-item-price-sum><?= $item['PRICE_SUM_FORMATTED'] ?></div>
                <? if ($item['DISCOUNT_SUM']) { ?>
                    <div><?= Loc::getMessage('WC_BASKET_DISCOUNT') ?> <span
                                data-basket-item-discount-sum><?= $item['DISCOUNT_SUM_FORMATTED'] ?></span></div>
                <? } ?>
            </td>
        </tr>
        <tr class="restore-button hide" data-action-basket-item="plus">
            <td colspan="6">
                <?= Loc::getMessage('WC_BASKET_RESTORE_BUTTON') ?>
            </td>
        </tr>
    <? } ?>
</table>

<table id="wc-basket-container">
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_WEIGHT') ?></td>
        <td data-basket-weight><?= $arResult['INFO']['WEIGHT_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_COUNT') ?></td>
        <td data-basket-count><?= $arResult['INFO']['COUNT'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_VAT') ?></td>
        <td data-basket-vat><?= $arResult['INFO']['VAT_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_PRICE_BASE') ?></td>
        <td data-basket-price-base><?= $arResult['INFO']['PRICE_BASE_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_DISCOUNT') ?></td>
        <td data-basket-discount><?= $arResult['INFO']['DISCOUNT_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_PRICE') ?></td>
        <td data-basket-price><?= $arResult['INFO']['PRICE_FORMATTED'] ?></td>
    </tr>
</table>

<script type="text/javascript">
    BX.ready(function () {
        if (!window.hasOwnProperty('WCSaleBasket')) {
            window.WCSaleBasket = new WCSaleBasket(<?=Bitrix\Main\Web\Json::encode([
                'basketHandlerClass' => $arParams['BASKET_HANDLER_CLASS'],
            ])?>);
        }
    });
</script>