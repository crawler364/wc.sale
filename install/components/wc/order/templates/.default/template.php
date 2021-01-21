<?php

use Bitrix\Main\Localization\Loc;

?>
<form id="wc-order" action="" method="post">
    <h2><?= Loc::getMessage('WC_ORDER_PERSON_TYPE') ?></h2>
    <div>
        <? foreach ($arResult['PERSON_TYPES'] as $personType) { ?>
            <label>
                <input type="radio" value="<?= $personType['ID'] ?>" name="person_type_id">
                <?= $personType['NAME'] ?>
            </label>
        <? } ?>
    </div>

    <h2><?= Loc::getMessage('WC_ORDER_TEMPLATE_PROPERTIES_TITLE') ?></h2>
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
                                   value="<?= $property['VALUE'][0] ?>">
                        <? } ?>
                </td>
            </tr>
        <? } ?>
    </table>

    <button type="submit"><?= Loc::getMessage('WC_ORDER_SUBMIT_BUTTON') ?></button>
</form>

<script type="text/javascript">
    if (!window.hasOwnProperty('WCSaleOrder')) {
        window.WCSaleOrder = new WCSaleOrder(<?=Bitrix\Main\Web\Json::encode([
        ])?>);
        window.WCSaleOrder.init();
    }
</script>