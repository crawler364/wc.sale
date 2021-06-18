<?php

if ($this->GetPageName() === 'template_accepted') {
    if ($arResult['DATA']["FIELDS"]["DATE_INSERT"] instanceof \Bitrix\Main\Type\DateTime) {
        $arResult['DATA']['FIELDS']['DATE_INSERT'] = $arResult['DATA']["FIELDS"]["DATE_INSERT"]->toUserTime()->format('d.m.Y H:i');
    }

    if ((int)$arResult['DATA']["PAYMENT"]["LOGOTIP"] > 0) {
        $arResult['DATA']["PAYMENT"]["LOGO"] = CFile::GetPath($arResult['DATA']["PAYMENT"]["LOGOTIP"]);
    }
}

