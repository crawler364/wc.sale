<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'PARAMETERS' => [
        'BASKET_HANDLER_CLASS' => [
            'NAME' => GetMessage('WC_BASKET_HANDLER_CLASS'),
            'TYPE' => 'STRING',
            'PARENT' => 'BASE',
        ],
    ],
];