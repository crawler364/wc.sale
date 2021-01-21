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
                        <input type="radio" value="<?= $personType['ID'] ?>"
                               name="PERSON_TYPE_ID" <?= $personType['CHECKED'] ? 'checked' : '' ?> data-person-type-id>
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

    <h2><?= Loc::getMessage('WC_DELIVERIES_TITLE') ?></h2>
    <table>
        <? foreach ($arResult['DELIVERIES'] as $delivery) { ?>
            <tr>
                <td>
                    <label>
                        <input type="radio" name="DELIVERY_ID"
                               value="<?= $delivery['ID'] ?>" <?= $delivery['CHECKED'] ? 'checked' : '' ?>
                               data-delivery-id>
                        <?= $delivery['NAME'] ?>
                    </label>
                </td>
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