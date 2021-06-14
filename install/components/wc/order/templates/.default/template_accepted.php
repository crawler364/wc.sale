<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(false);

use Bitrix\Main\Localization\Loc;

$data = $arResult['DATA'];
$errors = $arResult['ERRORS'];
?>

<h1><?= Loc::getMessage('WC_ORDER_ACCEPTED_TITLE') ?></h1>
<p><?= Loc::getMessage('WC_ORDER_ACCEPTED_TEXT1', ['#ID#' => $data['FIELDS']['ID'], '#DATE#' => $data['FIELDS']['DATE_INSERT']]) ?></p>
<p><?= Loc::getMessage('WC_ORDER_ACCEPTED_TEXT2', ['#LINK#' => '/personal/']) ?></p>

<h2><?= Loc::getMessage('WC_ORDER_ACCEPTED_PAYMENT_TITLE') ?></h2>
<p><img src="<?= $data['PAYMENT']['LOGO'] ?>" width="100" alt="<?= $data['PAYMENT']['PAY_SYSTEM_NAME'] ?>"></p>
<p><?= Loc::getMessage('WC_ORDER_ACCEPTED_PAYMENT_TEXT1', ['#PAYSYSTEM#' => $data['PAYMENT']['PAY_SYSTEM_NAME'], '#ID#' => $data['PAYMENT']['ACCOUNT_NUMBER']]) ?></p>
<?= $arResult['DATA']['PAYMENT']["BUFFERED_OUTPUT"] ?>

<h2><?= Loc::getMessage('WC_ORDER_ACCEPTED_SHIPMENT_TITLE') ?></h2>
<p><img src="<?= $data['SHIPMENT']['LOGO'] ?>" width="100" alt="<?= $data['SHIPMENT']['DELIVERY_NAME'] ?>"></p>
<p><?= Loc::getMessage('WC_ORDER_ACCEPTED_SHIPMENT_TEXT1', ['#DELIVERY#' => $data['SHIPMENT']['DELIVERY_NAME']]) ?></p>
