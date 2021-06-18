<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;

?>
<h1><?= Loc::getMessage("WC_ORDER_TITLE") ?></h1>

<div>
    <?= Loc::getMessage("WC_ORDER_EMPTY_BASKET") ?>
</div>
