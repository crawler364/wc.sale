<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main\Localization\Loc;

?>
<table id="wc-basket">
  <thead>
  <tr>
    <td><?= Loc::getMessage('DETAIL_PICTURE') ?></td>
    <td><?= Loc::getMessage('ARTICLE') ?></td>
    <td><?= Loc::getMessage('NAME') ?></td>
    <td><?= Loc::getMessage('PRICE') ?></td>
    <td></td>
    <td><?= Loc::getMessage('PRICE_SUM') ?></td>
  </tr>
  </thead>
    <? foreach ($arResult['ITEMS'] as $item) { ?>
      <tr data-basket-item-id="<?= $item['PRODUCT_ID'] ?>">
        <td>
          <img class="detail-picture" src="<?= $item['ELEMENT']['DETAIL_PICTURE'] ?>"
               alt="<?= Loc::getMessage('NAME') ?>">
        </td>
        <td><?= $item['ELEMENT']['PROPERTY_ARTICLE_VALUE'] ?></td>
        <td><?= $item['NAME'] ?></td>
        <td data-basket-item-price><?= $item['PRICE_FORMATTED'] ?></td>
        <td>
          <table>
            <tr>
              <td data-basket-item-action="minus">-</td>
              <td>
                <label>
                  <input type="text" data-basket-item-action="set" value="<?= $item['QUANTITY'] ?>">
                </label>
              </td>
              <td data-basket-item-action="plus">+</td>
              <td data-basket-item-action="delete">x</td>
            </tr>
          </table>
        </td>
        <td data-basket-item-price-sum><?= $item['PRICE_SUM_FORMATTED'] ?></td>
      </tr>
    <? } ?>
  <tr>
    <td colspan="4"></td>
    <td><?= Loc::getMessage('BASKET_WEIGHT') ?></td>
    <td data-basket-price><?= $arResult['INFO']['WEIGHT_FORMATTED'] ?></td>
  </tr>
  <tr>
    <td colspan="4"></td>
    <td><?= Loc::getMessage('BASKET_COUNT') ?></td>
    <td data-basket-price><?= $arResult['INFO']['COUNT'] ?></td>
  </tr>
  <tr>
    <td colspan="4"></td>
    <td><?= Loc::getMessage('BASKET_VAT') ?></td>
    <td data-basket-price><?= $arResult['INFO']['VAT_FORMATTED'] ?></td>
  </tr>
  <tr>
    <td colspan="4"></td>
    <td><?= Loc::getMessage('BASKET_PRICE') ?></td>
    <td data-basket-price><?= $arResult['INFO']['PRICE_FORMATTED'] ?></td>
  </tr>
  <tr>
    <td colspan="4"></td>
    <td><?= Loc::getMessage('BASKET_DISCOUNT') ?></td>
    <td data-basket-price><?= $arResult['INFO']['PRICE_FORMATTED'] ?></td>
  </tr>
  <tr>
    <td colspan="4"></td>
    <td><?= Loc::getMessage('BASKET_PRICE_TOTAL') ?></td>
    <td data-basket-price-total></td>
  </tr>
</table>

<script type="text/javascript">
    WCSaleBasket = new WCSaleBasket(<?=Bitrix\Main\Web\Json::encode([
        'basketHandlerClass' => $arParams['BASKET_HANDLER_CLASS'],
    ])?>);
</script>