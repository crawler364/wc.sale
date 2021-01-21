<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
Bitrix\Main\Loader::includeModule('wc.sale');

global $APPLICATION;

$APPLICATION->IncludeComponent(
    "wc:order",
    ".default",
    array(
        "COMPONENT_TEMPLATE" => ".default",
    ),
    false
);