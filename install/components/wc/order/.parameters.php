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
            'NAME' => GetMessage('WC_ORDER_HANDLER_CLASS'),
            'TYPE' => 'STRING',
            'PARENT' => 'BASE',
        ],
        'AJAX_MODE' => [
            'NAME' => GetMessage('WC_ORDER_AJAX_MODE'),
            'PARENT' => 'AJAX_SETTINGS',
            'TYPE' => 'CHECKBOX',
            'VALUE' => 'Y',
            "DEFAULT" => "Y",
            "HIDDEN" => 'Y',
        ],
        'AJAX_OPTION_JUMP' => [
            'NAME' => GetMessage('WC_ORDER_AJAX_OPTION_JUMP'),
            'PARENT' => 'AJAX_SETTINGS',
            'TYPE' => 'CHECKBOX',
            'VALUE' => 'Y',
            "DEFAULT" => "Y",
        ],
        'AJAX_OPTION_STYLE' => [
            'NAME' => GetMessage('WC_ORDER_AJAX_OPTION_STYLE'),
            'PARENT' => 'AJAX_SETTINGS',
            'TYPE' => 'CHECKBOX',
            'VALUE' => 'Y',
            "DEFAULT" => "Y",
        ],
    ],
];
