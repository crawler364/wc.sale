<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div id="basket">
  <div data-basket-product-id="123">
    <div class='lol' data-basket-action="plus">+</div>
    <input type="text" data-basket-action="set">
    <div data-basket-action="minus">-</div>
    <div data-basket-action="delete">X</div>
  </div>

  <div data-basket-product-id="124">
    <div class='lol' data-basket-action="plus">+</div>
    <input type="text" data-basket-action="set">
    <div data-basket-action="minus">-</div>
    <div data-basket-action="delete">X</div>
  </div>


</div>

<script type="text/javascript">
    WCSaleBasket = new WCSaleBasket();


</script>
