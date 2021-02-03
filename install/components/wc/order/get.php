<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
Bitrix\Main\Loader::includeModule('wc.sale');

?>

<?
$APPLICATION->IncludeComponent(
    "wc:order",
    ".default",
    [
        "COMPONENT_TEMPLATE" => ".default",
    ],
    false
); ?>
