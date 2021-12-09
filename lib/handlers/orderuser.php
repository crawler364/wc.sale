<?php


namespace WC\Sale\Handlers;


use Bitrix\Sale\PropertyValueCollection;
use WC\Core\Bitrix\Main\Result;

class OrderUser
{
    public static function autoRegister($pc): Result
    {
        $result = new Result;

        if ($pc instanceof PropertyValueCollection && $emailProperty = $pc->getItemByOrderPropertyCode('EMAIL')) {
            $email = $emailProperty->getValue();
        }
        $password = uniqid();
        $user = new \CUser;

        $id = $user->Add([
            'LOGIN' => $email,
            'EMAIL' => $email,
            'PASSWORD' => $password,
            'CONFIRM_PASSWORD' => $password,
        ]);

        if ((int)$id > 0) {
            global $USER;
            $USER->Authorize($id);
            $result->setData(['ID' => $id]);
        } else {
            $result->addError($user->LAST_ERROR);
        }

        return $result;
    }
}
