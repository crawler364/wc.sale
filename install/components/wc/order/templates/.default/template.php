<?php

use Bitrix\Main\Localization\Loc;

$a = $arResult;

?>
<form action="" method="post">
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
    <? foreach ($arResult['PROPERTIES'] as $property) { ?>
        <?=$property['NAME']?>

    <? } ?>

</form>