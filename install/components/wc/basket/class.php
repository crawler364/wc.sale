<?php

use Bitrix\Main\SystemException;

class WCSaleBasket extends CBitrixComponent implements Bitrix\Main\Engine\Contract\Controllerable
{
    public function configureActions()
    {
        // TODO: Implement configureActions() method.
    }

    public function executeComponent()
    {
        try {
            $this->orderHandler = new \WC\Sale\OrderHandler();
        } catch (SystemException $exception) {
        }

        $this->includeComponentTemplate();
    }
}