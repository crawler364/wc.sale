<?php


namespace WC\Sale\Handlers\Internals;


use WC\Core\Bitrix\Main\Result;
use WC\Sale\Order;

interface OrderInterface
{
    /**
     * @param int|null $userId
     * @return Order|\Bitrix\Sale\Order
     */
    public static function createOrder($userId = null);

    /**
     * @return Result
     */
    public function processOrder(): Result;

    /**
     * @return Result
     */
    public function saveOrder(): Result;
}
