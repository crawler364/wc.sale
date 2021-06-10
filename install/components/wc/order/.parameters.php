<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'GROUPS' => [
        'BASE' => [
            'NAME' => GetMessage('COMP_FORM_GROUP_PARAMS'),
        ],
        'AJAX_SETTINGS' => [
            'NAME' => GetMessage('WC_ORDER_AJAX_SETTINGS'),
        ],
    ],
    'PARAMETERS' => [
        'ORDER_HANDLER_CLASS' => [
            'PARENT' => 'BASE',
            'NAME' => GetMessage('WC_ORDER_HANDLER_CLASS'),
            'TYPE' => 'STRING',
        ],
        'ALLOW_AUTO_REGISTER' => [
            'PARENT' => 'BASE',
            'NAME' => GetMessage('WC_ORDER_ALLOW_AUTO_REGISTER'),
            'TYPE' => 'CHECKBOX',
            "DEFAULT" => "Y",
        ],
        'AJAX_MODE' => [
            'PARENT' => 'AJAX_SETTINGS',
            'NAME' => GetMessage('WC_ORDER_AJAX_MODE'),
            'TYPE' => 'CHECKBOX',
            'VALUE' => 'Y',
            "DEFAULT" => "Y",
            "HIDDEN" => 'Y',
        ],
        'AJAX_OPTION_JUMP' => [
            'PARENT' => 'AJAX_SETTINGS',
            'NAME' => GetMessage('WC_ORDER_AJAX_OPTION_JUMP'),
            'TYPE' => 'CHECKBOX',
            'VALUE' => 'Y',
            "DEFAULT" => "Y",
        ],
        'AJAX_OPTION_STYLE' => [
            'PARENT' => 'AJAX_SETTINGS',
            'NAME' => GetMessage('WC_ORDER_AJAX_OPTION_STYLE'),
            'TYPE' => 'CHECKBOX',
            'VALUE' => 'Y',
            "DEFAULT" => "Y",
        ],
    ],
];
