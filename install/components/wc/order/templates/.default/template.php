<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$this->setFrameMode(false);

$data = $arResult['DATA'];
$errors = $arResult['ERRORS'];

?>
<h1><?= Loc::getMessage("WC_ORDER_TITLE") ?></h1>
<? if ($arParams['SHOW_BASKET'] === 'Y') {
    $APPLICATION->IncludeComponent(
        "wc:basket",
        ".default",
        [
            "COMPONENT_TEMPLATE" => ".default",
            "BASKET_HANDLER_CLASS" => "",
            "PROPERTIES" => [
            ],
            "SHOW_FIELDS" => "N",
        ],
        false
    );
} ?>
<div data-container="errors" class="errors">
    <? foreach ($errors as $error) {
        echo $error;
    } ?>
</div>
<form data-container="order" action="" method="post">
    <h2><?= Loc::getMessage('WC_ORDER_LOCATION_TITLE') ?></h2>
    <div>
        <label for="<?= $data['LOCATION']['CODE'] ?>"></label>
        <? $APPLICATION->IncludeComponent(
            "bitrix:sale.location.selector.search",
            "",
            [
                "COMPONENT_TEMPLATE" => ".default",
                "ID" => "",
                "CODE" => $data['LOCATION']['VALUE'],
                "INPUT_NAME" => $data['LOCATION']['CODE'],
                "PROVIDE_LINK_BY" => "code",
                "JSCONTROL_GLOBAL_ID" => "",
                "JS_CALLBACK" => "",
                "FILTER_BY_SITE" => "Y",
                "SHOW_DEFAULT_LOCATIONS" => "Y",
                "CACHE_TYPE" => "A",
                "CACHE_TIME" => "36000000",
                "FILTER_SITE_ID" => "s1",
                "INITIALIZE_BY_GLOBAL_EVENT" => "",
                "SUPPRESS_ERRORS" => "N",
            ]
        ); ?>
    </div>

    <h2><?= Loc::getMessage('WC_ORDER_PERSON_TYPE_TITLE') ?></h2>
    <table class="person-type">
        <? foreach ($data['PERSON_TYPES'] as $personType) { ?>
            <tr>
                <td>
                    <label>
                        <input type="radio" value="<?= $personType['ID'] ?>" name="PERSON_TYPE_ID"
                            <?= $personType['CHECKED'] ? 'checked' : '' ?> data-action="refresh">
                        <?= $personType['NAME'] ?>
                    </label>
                </td>
            </tr>
        <? } ?>
    </table>

    <h2><?= Loc::getMessage('WC_ORDER_DELIVERIES_TITLE') ?></h2>
    <table>
        <? foreach ($data['DELIVERIES'] as $delivery) { ?>
            <tr>
                <td>
                    <label>
                        <input type="radio" name="DELIVERY_ID" value="<?= $delivery['ID'] ?>"
                            <?= $delivery['CHECKED'] ? 'checked' : '' ?> data-action="refresh">
                        <?= $delivery['NAME'] ?>
                    </label>
                </td>
            </tr>
        <? } ?>
    </table>

    <h2><?= Loc::getMessage('WC_ORDER_PAY_SYSTEMS_TITLE') ?>:</h2>
    <table>
        <? foreach ($data['PAY_SYSTEMS'] as $paySystem) { ?>
            <tr>
                <td>
                    <label>
                        <input type="radio" name="PAY_SYSTEM_ID" value="<?= $paySystem['ID'] ?>"
                            <?= $paySystem['CHECKED'] ? 'checked' : '' ?> data-action="refresh">
                        <?= $paySystem['NAME'] ?>
                    </label>
                </td>
            </tr>
        <? } ?>
    </table>

    <h2><?= Loc::getMessage('WC_ORDER_PROPERTIES_TITLE') ?></h2>
    <table>
        <? foreach ($data['PROPERTIES'] as $property) { ?>
            <tr>
                <td>
                    <label for="<?= $property['CODE'] ?>">
                        <?= $property['NAME'] ?>
                        <?= $property['REQUIRED'] == 'Y' ? '*' : '' ?>
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
        <? foreach ($data['BASKET_LIST'] as $product) { ?>
            <tr>
                <td><?= $product['NAME'] ?></td>
                <td><?= $product['PRICE_FORMATTED'] ?></td>
                <td><?= $product['QUANTITY'] ?> <?= $product['MEASURE_NAME'] ?></td>
                <td><?= $product['PRICE_SUM_FORMATTED'] ?></td>
            </tr>
        <? } ?>
    </table>

    <div data-action="submit" class="button-submit"><?= Loc::getMessage('WC_ORDER_SUBMIT_BUTTON') ?></div>
</form>

<table data-container="wc-basket" class="wc-basket-container">
    <tbody>
    <tr>
        <td><?= Loc::getMessage('WC_ORDER_FIELDS_WEIGHT') ?></td>
        <td data-container="basket-weight"><?= $data['BASKET_FIELDS']['WEIGHT_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_ORDER_FIELDS_COUNT') ?></td>
        <td data-container="basket-count"><?= $data['BASKET_FIELDS']['COUNT'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_ORDER_FIELDS_VAT_SUM') ?></td>
        <td data-container="basket-vat-sum"><?= $data['BASKET_FIELDS']['VAT_SUM_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_ORDER_FIELDS_BASKET_BASE_PRICE') ?></td>
        <td data-container="basket-base-price"><?= $data['BASKET_FIELDS']['BASE_PRICE_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_ORDER_FIELDS_DISCOUNT') ?></td>
        <td data-container="basket-discount-price"><?= $data['BASKET_FIELDS']['DISCOUNT_PRICE_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_ORDER_FIELDS_BASKET_PRICE') ?></td>
        <td data-container="basket-price"><?= $data['BASKET_FIELDS']['PRICE_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_ORDER_FIELDS_PRICE_DELIVERY') ?></td>
        <td><?= $data['FIELDS']['PRICE_DELIVERY_FORMATTED'] ?></td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('WC_ORDER_FIELDS_PRICE') ?></td>
        <td><?= $data['FIELDS']['PRICE_FORMATTED'] ?></td>
    </tr>
    </tbody>
    <tr>
        <td data-container="basket-empty" class="hide"><?= Loc::getMessage('WC_BASKET_EMPTY') ?></td>
    </tr>
</table>

<script type="text/javascript">
    if (!window.hasOwnProperty('WCSaleOrder')) {
        window.WCSaleOrder = new WCSaleOrder(<?=Bitrix\Main\Web\Json::encode([
            'parameters' => [
                'ajaxId' => $arParams['AJAX_ID'],
                'ORDER_HANDLER_CLASS' => $arParams['ORDER_HANDLER_CLASS'],
            ],
            'signedParameters' => $component->getSignedParameters(),
        ])?>);
        window.WCSaleOrder.init();
    }
</script>
