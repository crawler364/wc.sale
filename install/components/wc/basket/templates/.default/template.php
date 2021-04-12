<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main\Localization\Loc;
?>
<table class="wc-basket-items-container">
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
        <tbody data-basket-item-container data-basket-item-id="<?= $item['PRODUCT_ID'] ?>">
        <tr>
            <td data-basket-item-restore-button data-action-basket-item="plus" class="restore-button hide" colspan="6">
                <?= Loc::getMessage('WC_BASKET_RESTORE_BUTTON') ?>
            </td>
        </tr>
        <tr data-basket-item>
            <td>
                <img class="detail-picture" src="<?= $item['ELEMENT']['DETAIL_PICTURE'] ?>"
                     alt="<?= Loc::getMessage('WC_BASKET_NAME') ?>">
            </td>
            <td><?= $item['ELEMENT']['PROPERTY_ARTICLE_VALUE'] ?></td>
            <td><?= $item['NAME'] ?></td>
            <td>
                <? if ($item['DISCOUNT_PRICE']) { ?>
                    <div class="line-through"><?= $item['BASE_PRICE_FORMATTED'] ?></div>
                <? } ?>
                <div><?= $item['PRICE_FORMATTED'] ?></div>
            </td>
            <td>
                <table>
                    <tr>
                        <td data-action-basket-item="minus">-</td>
                        <td>
                            <label>
                                <input type="text" data-action-basket-item="set"
                                       value="<?= $item['QUANTITY'] ?>">
                            </label>
                        </td>
                        <td data-action-basket-item="plus">+</td>
                        <td data-action-basket-item="delete">x</td>
                    </tr>
                </table>
            </td>
            <td>
                <? if ($item['DISCOUNT_PRICE_SUM']) { ?>
                    <div class="line-through"
                         data-basket-item-base-price-sum><?= $item['BASE_PRICE_SUM_FORMATTED'] ?>
                    </div>
                <? } ?>
                <div data-basket-item-price-sum><?= $item['PRICE_SUM_FORMATTED'] ?></div>
                <? if ($item['DISCOUNT_PRICE_SUM']) { ?>
                    <div><?= Loc::getMessage('WC_BASKET_DISCOUNT_PRICE') ?>
                        <span data-basket-item-discount-price-sum><?= $item['DISCOUNT_PRICE_SUM_FORMATTED'] ?></span>
                    </div>
                <? } ?>
            </td>
        </tr>
        </tbody>
    <? } ?>
</table>

<table data-wc-basket-container class="wc-basket-container">
    <tbody>
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
        <td><?= Loc::getMessage('WC_BASKET_BASE_PRICE') ?></td>
        <td data-basket-base-price><?= $arResult['INFO']['BASE_PRICE_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_DISCOUNT_PRICE') ?></td>
        <td data-basket-discount-price><?= $arResult['INFO']['DISCOUNT_PRICE_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_PRICE') ?></td>
        <td data-basket-price><?= $arResult['INFO']['PRICE_FORMATTED'] ?></td>
    </tr>
    </tbody>
    <tr>
        <td data-basket-empty class="hide"><?= Loc::getMessage('WC_BASKET_EMPTY') ?></td>
    </tr>
</table>

<script type="text/javascript">
    if (!window.hasOwnProperty('WCSaleBasket')) {
        window.WCSaleBasket = new WCSaleBasket(<?=Bitrix\Main\Web\Json::encode([
            'basketHandlerClass' => $arParams['BASKET_HANDLER_CLASS'],
        ])?>);
        window.WCSaleBasket.init();
    }
</script>
