<?php
/**
 * Created by PhpStorm.
 * User: WilliamWard
 */

namespace GivePay\Gateway\Transactions;

final class Order
{

    /**
     * @var string The order ID
     */
    private $orderId;

    /**
     * Order constructor.
     * @param string $orderId The order Id for the order
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Serializes the order GPG API object
     * @return array serialized object
     */
    public function serialize()
    {
        return [
            'order_number' => $this->orderId
        ];
    }
}