<?php


namespace WC\Sale\Handlers;


use Bitrix\Sale\PropertyValueCollection;

class OrderUser
{
    public static function autoRegister(array $fields)
    {
        $pc = $fields[0];
        if ($fields[0] instanceof PropertyValueCollection && $emailProperty = $pc->getItemByOrderPropertyCode('EMAIL')) {
            $email = $emailProperty->getValue();
        }
        $password = uniqid();
        $user = new \CUser;

        $result = $user->Add([
            'LOGIN' => $email,
            'EMAIL' => $email,
            'PASSWORD' => $password,
            'CONFIRM_PASSWORD' => $password,
        ]);

        if ((int)$result > 0) {
            global $USER;
            $USER->Authorize($result);
        }

        return $result;
    }
}
