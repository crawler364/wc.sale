<?php

if (!defined('WC_SALE_BASKET_DOM_HANDLER')) {
    define('WC_SALE_BASKET_DOM_HANDLER', TRUE);
    Bitrix\Main\Page\Asset::getInstance()->addJs("$templateFolder/domhandler.js");
}
