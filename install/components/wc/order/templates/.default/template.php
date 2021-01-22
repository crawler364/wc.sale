<?php

use Bitrix\Main\Localization\Loc;

?>
<form id="wc-order" action="" method="post">
    <h2><?= Loc::getMessage('WC_ORDER_PERSON_TYPE') ?></h2>
    <table>
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

<script type="text/javascript">
    if (!window.hasOwnProperty('WCSaleOrder')) {
        window.WCSaleOrder = new WCSaleOrder(<?=Bitrix\Main\Web\Json::encode([
        ])?>);
        window.WCSaleOrder.init();
    }
</script>