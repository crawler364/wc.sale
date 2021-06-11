<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

$APPLICATION->IncludeComponent("bitrix:main.auth.form", ".default");
