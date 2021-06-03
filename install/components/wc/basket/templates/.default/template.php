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
        <tbody data-container="basket-item-wrap" data-basket-item-id="<?= $item['PRODUCT_ID'] ?>">
        <tr>
            <td data-container="basket-item-restore-button" data-basket-item-action="plus" class="restore-button hide"
                colspan="6">
                <?= Loc::getMessage('WC_BASKET_RESTORE_BUTTON') ?>
            </td>
        </tr>
        <tr data-container="basket-item">
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
                        <td data-basket-item-action="minus">-</td>
                        <td>
                            <label>
                                <input type="text" data-basket-item-action="set"
                                       value="<?= $item['QUANTITY'] ?>">
                            </label>
                        </td>
                        <td data-basket-item-action="plus">+</td>
                        <td data-basket-item-action="delete">x</td>
                    </tr>
                </table>
            </td>
            <td>
                <? if ($item['DISCOUNT_PRICE_SUM']) { ?>
                    <div class="line-through"
                         data-container="basket-item-base-price-sum"><?= $item['BASE_PRICE_SUM_FORMATTED'] ?>
                    </div>
                <? } ?>
                <div data-container="basket-item-price-sum"><?= $item['PRICE_SUM_FORMATTED'] ?></div>
                <? if ($item['DISCOUNT_PRICE_SUM']) { ?>
                    <div><?= Loc::getMessage('WC_BASKET_DISCOUNT') ?>
                        <span data-container="basket-item-discount-price-sum"><?= $item['DISCOUNT_PRICE_SUM_FORMATTED'] ?></span>
                    </div>
                <? } ?>
            </td>
        </tr>
        </tbody>
    <? } ?>
</table>

<table data-container="wc-basket" class="wc-basket-container">
    <tbody>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_INFO_WEIGHT') ?></td>
        <td data-container="basket-weight"><?= $arResult['INFO']['WEIGHT_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_INFO_COUNT') ?></td>
        <td data-container="basket-count"><?= $arResult['INFO']['COUNT'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_INFO_VAT_SUM') ?></td>
        <td data-container="basket-vat-sum"><?= $arResult['INFO']['VAT_SUM_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_INFO_PRICE') ?></td>
        <td data-container="basket-base-price"><?= $arResult['INFO']['BASE_PRICE_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_INFO_DISCOUNT') ?></td>
        <td data-container="basket-discount-price"><?= $arResult['INFO']['DISCOUNT_PRICE_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_INFO_PRICE_SUM') ?></td>
        <td data-container="basket-price"><?= $arResult['INFO']['PRICE_FORMATTED'] ?></td>
    </tr>
    </tbody>
    <tr>
        <td data-container="basket-empty" class="hide"><?= Loc::getMessage('WC_BASKET_EMPTY') ?></td>
    </tr>
</table>

<script type="text/javascript">
    if (!window.hasOwnProperty('WCSaleBasket')) {
        window.WCSaleBasket = new WCSaleBasket(<?=Bitrix\Main\Web\Json::encode([
            'parameters' => [
                'BASKET_HANDLER_CLASS' => $arParams['BASKET_HANDLER_CLASS'],
                'PROPERTIES' => $arParams['PROPERTIES'],
            ],
            'signedParameters' => $this->getComponent()->getSignedParameters(),
        ])?>);
        window.WCSaleBasket.init();
    }
</script>
