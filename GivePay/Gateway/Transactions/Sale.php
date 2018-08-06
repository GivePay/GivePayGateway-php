<?php
/**
 * Created by PhpStorm.
 * User: WilliamWard
 * Date: 8/6/2018
 * Time: 8:40 AM
 */

namespace GivePay\Gateway\Transactions;

final class Sale {
    /**
     * @var int The total amount of the transactions
     */
    private $total;

    /**
     * @var string The type of terminal
     */
    private $terminal_type = 'com.givepay.terminal-types.ecommerce';

    /**
     * @var Address The billing address
     */
    private $billing_address;

    /**
     * @var string Billing email address
     */
    private $email;

    /**
     * @var string Billing phone
     */
    private $phone;

    /**
     * @var Card The card to used for this transaction
     */
    private $card;

    /**
     * Sale constructor.
     * @param int $total
     * @param string $terminal_type
     * @param Address $billing_address
     * @param string $email
     * @param string $phone
     * @param Card $card
     */
    public function __construct($total, $terminal_type, $billing_address, $email, $phone, $card)
    {
        $this->total = $total;
        $this->terminal_type = $terminal_type;
        $this->billing_address = $billing_address;
        $this->email = $email;
        $this->phone = $phone;
        $this->card = $card;
    }

    /**
     * @return float The total amount for the sale in cents
     */
    public function getTotal() {
        return floatval($this->total * 100);
    }

    /**
     * @param string $merchant_id
     * @param string $terminal_id
     * @return array
     */
    public function serialize($merchant_id, $terminal_id) {
        $sale_request = array(
            'mid'      => $merchant_id,
            'terminal' => array(
                'tid'           => $terminal_id,
                'terminal_type' => $this->terminal_type
			),
            'amount'   => array(
                'base_amount' => $this->getTotal()
            ),
            'payer'    => array(
                'billing_address' => $this->billing_address->serialize(),
                'email_address'   => $this->email,
                'phone_number'    => $this->phone
            ),
            'card' => $this->card->serialize()
        );

        return $sale_request;
    }
}