<?php


namespace WC\Sale\Handlers\Internals;


use WC\Core\Bitrix\Main\Result;
use WC\Sale\Order;

interface OrderInterface
{
    /**
     * OrderInterface constructor.
     * @param Order $order
     * @param array $data
     * @param array $params
     */
    public function __construct(Order $order, array $data = [], array $params = []);

    /**
     * @param int|null $userId
     * @return Order|\Bitrix\Sale\Order
     */
    public static function createOrder($userId = null);

    /**
     * @param array $filter
     * @return Result
     */
    public static function loadOrder(array $filter): Result;

    /**
     * @return Result
     */
    public function refreshOrder(): Result;

    /**
     * @return Result
     */
    public function getOrder(): Result;

    /**
     * @return Result
     */
    public function saveOrder(): Result;
}
