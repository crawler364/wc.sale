<?php


namespace WC\Sale\Handlers;


use Bitrix\Sale\PropertyValueCollection;

class OrderUser extends \WC\Core\Handlers\Internals\UserBase
{
    public static function autoRegister(array $fields): \CUser
    {
        if ($fields[0] instanceof PropertyValueCollection && $emailProperty = $pc->getItemByOrderPropertyCode('EMAIL')) {
            $email = $emailProperty->getValue();
        }
        $password = uniqid();

        $user = static::add([
            'LOGIN' => $email,
            'EMAIL' => $email,
            'PASSWORD' => $password,
            'CONFIRM_PASSWORD' => $password,
        ]);

        if ($userId = $user->GetID()) {
            global $USER;
            $USER->Authorize($userId);
        }

        return $user;
    }
}
