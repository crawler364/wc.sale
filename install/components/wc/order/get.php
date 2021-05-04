<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
Bitrix\Main\Loader::includeModule('wc.sale');

?>

<?php
$APPLICATION->IncludeComponent(
    "wc:basket",
    ".default",
    [
        "COMPONENT_TEMPLATE" => ".default",
        "BASKET_HANDLER_CLASS" => '',
    ],
    false
); ?>
<br><br><br>
<?
$APPLICATION->IncludeComponent(
    "wc:order",
    ".default",
    [
        "COMPONENT_TEMPLATE" => ".default",
        "PROPERTIES_DEFAULT_VALUE" => false,
    ],
    false
); ?>
