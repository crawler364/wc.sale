<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<table id="wc-basket">
  <thead>
  <tr>
    <td><?= getMessage('DETAIL_PICTURE') ?></td>
    <td><?= getMessage('ARTICLE') ?></td>
    <td><?= getMessage('NAME') ?></td>
    <td><?= getMessage('PRICE') ?></td>
    <td></td>
    <td><?= getMessage('PRICE_SUM') ?></td>
  </tr>
  </thead>
    <? foreach ($arResult['ITEMS'] as $item) { ?>
      <tr data-basket-product-id="<?= $item['PRODUCT_ID'] ?>">
        <td>
          <img class="detail-picture" src="<?= $item['ELEMENT']['DETAIL_PICTURE'] ?>" alt="<?= getMessage('NAME') ?>">
        </td>
        <td><?= $item['ELEMENT']['PROPERTY_ARTICLE_VALUE'] ?></td>
        <td><?= $item['NAME'] ?></td>
        <td><?= $item['PRICE_FORMATTED'] ?></td>
        <td>
          <table>
            <tr>
              <td data-basket-action="minus">-</td>
              <td>
                <label>
                  <input type="text" data-basket-action="set" value="<?= $item['QUANTITY'] ?>">
                </label>
              </td>
              <td data-basket-action="plus">+</td>
              <td data-basket-action="delete">x</td>
            </tr>
          </table>
        </td>
        <td><?= $item['PRICE_SUM_FORMATTED'] ?></td>
      </tr>
    <? } ?>
  <tr>
    <td colspan="5"></td>
    <td><?= $arResult['INFO']['PRICE_FORMATTED'] ?></td>
  </tr>
</table>

<script type="text/javascript">
    WCSaleBasket = new WCSaleBasket(<?=Bitrix\Main\Web\Json::encode([
        'basketHandlerClass' => $arParams['BASKET_HANDLER_CLASS'],
    ])?>);
</script>
