<?php


namespace WC\Sale\Handlers\Order;


use WC\Core\Bitrix\Main\Result;
use WC\Sale\Order;

interface HandlerInterface
{
    /**
     * @param int|null $userId
     * @return Order|\Bitrix\Sale\Order
     */
    public static function createOrder(int $userId = null);

    /**
     * @return Result
     */
    public function processOrder(): Result;

    /**
     * @return Result
     */
    public function saveOrder(): Result;
}
