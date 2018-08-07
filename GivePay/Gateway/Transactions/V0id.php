<?php
/**
 * Created by PhpStorm.
 * User: WilliamWard
 * Date: 8/6/2018
 * Time: 3:17 PM
 */

namespace GivePay\Gateway\Transactions;


final class V0id
{

    /**
     * @var string The type of terminal
     */
    private $terminal_type = 'com.givepay.terminal-types.ecommerce';

    /**
     * @var string The ID of the transactions to void
     */
    private $transaction_id;

    /**
     * Void constructor.
     * @param string $terminal_type
     * @param string $transaction_id
     */
    public function __construct(string $terminal_type, string $transaction_id)
    {
        $this->terminal_type = $terminal_type;
        $this->transaction_id = $transaction_id;
    }

    /**
     * Serializes the void into a GPG request
     * @param string $merchant_id
     * @param string $terminal_id
     * @return array
     */
    public function serialize($merchant_id, $terminal_id)
    {
        return array(
            'mid' => $merchant_id,
            'terminal' => array(
                'tid' => $terminal_id,
                'terminal_type' => $this->getTerminalType()
            ),
            'transaction_id' => $this->getTransactionId()
        );
    }

    /**
     * @return string
     */
    public function getTerminalType(): string
    {
        return $this->terminal_type;
    }

    /**
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transaction_id;
    }
}