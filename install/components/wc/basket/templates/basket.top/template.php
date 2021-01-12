<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

?>

<div id="wc-basket-top-container">
    <div data-basket-count><?= $arResult['INFO']['COUNT'] ?></div>
    <div data-basket-price><?= $arResult['INFO']['PRICE_FORMATTED'] ?></div>
</div>

<script type="text/javascript">
    BX.ready(function () {
        if (!window.hasOwnProperty('WCSaleBasket')) {
            window.WCSaleBasket = new WCSaleBasket(<?=Bitrix\Main\Web\Json::encode([
                'basketHandlerClass' => $arParams['BASKET_HANDLER_CLASS'],
            ])?>);
        }
    });
</script>