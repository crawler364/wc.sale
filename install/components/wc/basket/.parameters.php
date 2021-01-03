<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'GROUPS' => [
        'BASE' => [
            'NAME' => GetMessage('COMP_FORM_GROUP_PARAMS'),
        ],
    ],

    'PARAMETERS' => [
        'BASKET_HANDLER' => [
            'NAME' => GetMessage('WC_BASKET_HANDLER'),
            'TYPE' => 'STRING',
            'PARENT' => 'BASE',
        ],
    ],
];