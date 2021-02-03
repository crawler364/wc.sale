<?php

use Bitrix\Main\Localization\Loc;

?>
<form id="wc-order" action="" method="post">
    <h2><?= Loc::getMessage('WC_ORDER_PERSON_TYPE') ?></h2>
    <table class="person-type">
        <? foreach ($arResult['PERSON_TYPES'] as $personType) { ?>
            <tr>
                <td>
                    <label>
                        <input type="radio" value="<?= $personType['ID'] ?>" name="PERSON_TYPE_ID"
                            <?= $personType['CHECKED'] ? 'checked' : '' ?> data-action-refresh>
                        <?= $personType['NAME'] ?>
                    </label>
                </td>
            </tr>
        <? } ?>
    </table>

    <h2><?= Loc::getMessage('WC_ORDER_PROPERTIES_TITLE') ?></h2>
    <table>
        <? foreach ($arResult['PROPERTIES'] as $property) { ?>
            <tr>
                <td>
                    <label for="<?= $property['CODE'] ?>">
                        <?= $property['NAME'] ?><br>
                    </label>
                </td>
                <td>
                    <? switch ($arProp['TYPE']) {
                        case 'LOCATION':
                        default: ?>
                            <input id="<?= $property['CODE'] ?>" type="text" name="<?= $property['CODE'] ?>"
                                   value="<?= $property['VALUE'] ?>">
                        <? } ?>
                </td>
            </tr>
        <? } ?>
    </table>

    <h2><?= Loc::getMessage('WC_ORDER_DELIVERIES_TITLE') ?></h2>
    <table>
        <? foreach ($arResult['DELIVERIES'] as $delivery) { ?>
            <tr>
                <td>
                    <label>
                        <input type="radio" name="DELIVERY_ID" value="<?= $delivery['ID'] ?>"
                            <?= $delivery['CHECKED'] ? 'checked' : '' ?> data-action-refresh>
                        <?= $delivery['NAME'] ?>
                    </label>
                </td>
            </tr>
        <? } ?>
    </table>

    <h2><?= Loc::getMessage('WC_ORDER_PAY_SYSTEMS_TITLE') ?>:</h2>
    <table>
        <? foreach ($arResult['PAY_SYSTEMS'] as $paySystem) { ?>
            <tr>
                <td>
                    <label>
                        <input type="radio" name="PAY_SYSTEM_ID" value="<?= $paySystem['ID'] ?>"
                            <?= $paySystem['CHECKED'] ? 'checked' : '' ?> data-action-refresh>
                        <?= $paySystem['NAME'] ?>
                    </label>
                </td>
            </tr>
        <? } ?>
    </table>

    <h2><?= Loc::getMessage('WC_ORDER_PRODUCT_LIST_TITLE') ?></h2>
    <table class="product-list">
        <tr>
            <td><?= Loc::getMessage('WC_ORDER_PRODUCT_LIST_NAME') ?></td>
            <td><?= Loc::getMessage('WC_ORDER_PRODUCT_LIST_PRICE') ?></td>
            <td><?= Loc::getMessage('WC_ORDER_PRODUCT_LIST_COUNT') ?></td>
            <td><?= Loc::getMessage('WC_ORDER_PRODUCT_LIST_PRICE_SUM') ?></td>
        </tr>
        <? foreach ($arResult['PRODUCT_LIST'] as $product) { ?>
            <tr>
                <td><?= $product['NAME'] ?></td>
                <td><?= $product['PRICE_FORMATTED'] ?></td>
                <td><?= $product['QUANTITY'] ?> <?= $product['MEASURE_NAME'] ?></td>
                <td><?= $product['PRICE_SUM_FORMATTED'] ?></td>
            </tr>
        <? } ?>
    </table>

    <button data-action-submit type="submit"><?= Loc::getMessage('WC_ORDER_SUBMIT_BUTTON') ?></button>
</form>

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
    </tbody>
    <tr>
        <td data-basket-empty class="hide"><?= Loc::getMessage('WC_BASKET_EMPTY') ?></td>
    </tr>
</table>

<script type="text/javascript">
    if (!window.hasOwnProperty('WCSaleOrder')) {
        window.WCSaleOrder = new WCSaleOrder(<?=Bitrix\Main\Web\Json::encode([
        ])?>);
        window.WCSaleOrder.init();
    }
</script>
