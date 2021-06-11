<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!defined('WC_SALE_ORDER_DOM_HANDLER')) {
    define('WC_SALE_ORDER_DOM_HANDLER', TRUE);
    Bitrix\Main\Page\Asset::getInstance()->addJs("$templateFolder/domhandler.js");
}
