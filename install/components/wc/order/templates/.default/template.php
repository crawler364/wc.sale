<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

?>
<div style="font-size: 20px; color: red">
    <? foreach ($arResult['ERRORS'] as $error) {
        echo $error;
    } ?>
</div>
<form id="wc-order-form" action="" method="post">
    <h2><?= Loc::getMessage('WC_ORDER_PERSON_TYPE') ?></h2>
    <table class="person-type">
        <? foreach ($arResult['DATA']['PERSON_TYPES'] as $personType) { ?>
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

    <h2><?= Loc::getMessage('WC_ORDER_DELIVERIES_TITLE') ?></h2>
    <table>
        <? foreach ($arResult['DATA']['DELIVERIES'] as $delivery) { ?>
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
        <? foreach ($arResult['DATA']['PAY_SYSTEMS'] as $paySystem) { ?>
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

    <h2><?= Loc::getMessage('WC_ORDER_PROPERTIES_TITLE') ?></h2>
    <table>
        <? foreach ($arResult['DATA']['PROPERTIES'] as $property) { ?>
            <tr>
                <td>
                    <label for="<?= $property['CODE'] ?>">
                        <?= $property['NAME'] ?><br>
                    </label>
                </td>
                <td>
                    <? switch ($property['TYPE']) {
                        case 'Y/N':
                            switch ($property['MULTIPLE']) {
                                case 'Y': ?>
                                    <table>
                                        <? foreach ($property['VALUE'] as $key => $value) { ?>
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="<?= $property['CODE'] . "[$key]" ?>"
                                                           value="N">
                                                    <label>
                                                        <input type="checkbox"
                                                               name="<?= $property['CODE'] . "[$key]" ?>"
                                                               value="Y" <?= $value == 'Y' ? 'checked' : '' ?>>
                                                    </label>
                                                </td>
                                            </tr>
                                        <? } ?>
                                        <? // todo add input button ? ?>
                                    </table>
                                    <? break;
                                case 'N': ?>
                                    <input type="hidden" name="<?= $property['CODE'] ?>" value="N">
                                    <input type="checkbox" id="<?= $property['CODE'] ?>" name="<?= $property['CODE'] ?>"
                                           value="Y" <?= $property['VALUE'] == 'Y' ? 'checked' : '' ?>>
                                    <? break;
                            }
                            break;
                        case 'ENUM':
                            switch ($property['MULTIELEMENT']) { // Показывать как список элементов
                                case 'Y':
                                    switch ($property['MULTIPLE']) {
                                        case 'Y': ?>
                                            <table>
                                                <? foreach ($property['OPTIONS'] as $enumCode => $enumName) { ?>
                                                    <tr>
                                                        <td>
                                                            <label>
                                                                <input type="checkbox" name="<?= $property['CODE'] ?>[]"
                                                                       value="<?= $enumCode ?>"
                                                                    <?= in_array($enumCode, $property['VALUE'], true) ? 'checked' : '' ?>>
                                                                <?= $enumName ?>
                                                            </label>
                                                        </td>
                                                    </tr>
                                                <? } ?>
                                            </table>
                                            <? break;
                                        case 'N': ?>
                                            <table>
                                                <? foreach ($property['OPTIONS'] as $enumCode => $enumName) { ?>
                                                    <tr>
                                                        <td>
                                                            <label>
                                                                <input type="radio" name="<?= $property['CODE'] ?>"
                                                                       value="<?= $enumCode ?>"
                                                                    <?= $property['VALUE'] == $enumCode ? 'checked' : '' ?>>
                                                                <?= $enumName ?>
                                                            </label>
                                                        </td>
                                                    </tr>
                                                <? } ?>
                                            </table>
                                            <? break;
                                    }
                                    break;
                                case 'N':
                                    switch ($property['MULTIPLE']) {
                                        case 'Y': ?>
                                            <select id="<?= $property['CODE'] ?>" name="<?= $property['CODE'] ?>[]"
                                                    multiple>
                                                <? foreach ($property['OPTIONS'] as $enumCode => $enumName) { ?>
                                                    <option value="<?= $enumCode ?>"
                                                        <?= in_array($enumCode, $property['VALUE'], true) ? 'selected' : '' ?>>
                                                        <?= $enumName ?>
                                                    </option>
                                                    <?
                                                } ?>
                                            </select>
                                            <? break;
                                        case 'N': ?>
                                            <select id="<?= $property['CODE'] ?>" name="<?= $property['CODE'] ?>">
                                                <? foreach ($property['OPTIONS'] as $enumCode => $enumName) { ?>
                                                    <option value="<?= $enumCode ?>"
                                                        <?= $property['VALUE'] == $enumCode ? 'selected' : '' ?>>
                                                        <?= $enumName ?>
                                                    </option>
                                                    <?
                                                } ?>
                                            </select>
                                            <? break;
                                    }
                                    break;
                            }
                            break;
                        case 'FILE':
                            switch ($property['MULTIPLE']) {
                                case 'Y': ?>
                                    <table>
                                        <? foreach ($property['VALUE'] as $key => $value) { ?>
                                            <tr>
                                                <td>
                                                    <label>
                                                        <input type="file" name="<?= $property['CODE'] ?>[]">
                                                    </label>
                                                </td>
                                            </tr>
                                        <? } ?>
                                    </table>
                                    <? // todo add input button ?>
                                    <? break;
                                case 'N': ?>
                                    <input type="file" id="<?= $property['CODE'] ?>" name="<?= $property['CODE'] ?>">
                                    <? break;
                            }
                            break;
                        case 'DATE':
                            switch ($property['MULTIPLE']) {
                                case 'Y': ?>
                                    <table>
                                        <? foreach ($property['VALUE'] as $key => $value) { ?>
                                            <tr>
                                                <td>
                                                    <label>
                                                        <? $APPLICATION->IncludeComponent(
                                                            'bitrix:main.calendar',
                                                            '',
                                                            [
                                                                'SHOW_INPUT' => 'Y',
                                                                'INPUT_NAME' => "{$property['CODE']}[$key]",
                                                                'INPUT_VALUE' => $value,
                                                                'SHOW_TIME' => 'Y',
                                                                'HIDE_TIMEBAR' => 'Y',
                                                            ]
                                                        ); ?>
                                                    </label>
                                                </td>
                                            </tr>
                                        <? } ?>
                                        <? // todo add input button ?>
                                    </table>
                                    <? break;
                                case 'N':
                                    $APPLICATION->IncludeComponent(
                                        'bitrix:main.calendar',
                                        '',
                                        [
                                            'SHOW_INPUT' => 'Y',
                                            'INPUT_NAME' => $property['CODE'],
                                            'INPUT_VALUE' => $property['VALUE'],
                                            'SHOW_TIME' => 'Y',
                                            'HIDE_TIMEBAR' => 'Y',
                                        ]
                                    );
                                    break;
                            }
                            break;
                        case 'LOCATION':
                            break;
                        default:
                            switch ($property['MULTIPLE']) {
                                case 'Y': ?>
                                    <table>
                                        <? foreach ($property['VALUE'] as $key => $value) { ?>
                                            <tr>
                                                <td>
                                                    <label>
                                                        <input type="text" name="<?= $property['CODE'] . "[$key]" ?>"
                                                               value="<?= $value ?>">
                                                    </label>
                                                </td>
                                            </tr>
                                        <? } ?>
                                        <? // todo add input button ?>
                                    </table>
                                    <? break;
                                case 'N': ?>
                                    <input type="text" id="<?= $property['CODE'] ?>" name="<?= $property['CODE'] ?>"
                                           value="<?= $property['VALUE'] ?>">
                                    <? break;
                            }
                    } ?>
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
        <? foreach ($arResult['DATA']['PRODUCTS_LIST'] as $product) { ?>
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
        <td data-basket-weight><?= $arResult['DATA']['INFO']['WEIGHT_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_COUNT') ?></td>
        <td data-basket-count><?= $arResult['DATA']['INFO']['COUNT'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_VAT') ?></td>
        <td data-basket-vat><?= $arResult['DATA']['INFO']['VAT_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_PRICE_BASE') ?></td>
        <td data-basket-price-base><?= $arResult['DATA']['INFO']['PRICE_BASE_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_DISCOUNT') ?></td>
        <td data-basket-discount><?= $arResult['DATA']['INFO']['DISCOUNT_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_BASKET_PRICE') ?></td>
        <td data-basket-price><?= $arResult['DATA']['INFO']['PRICE_FORMATTED'] ?></td>
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
