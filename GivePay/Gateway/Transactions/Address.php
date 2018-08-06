<?php
/**
 * Created by PhpStorm.
 * User: WilliamWard
 * Date: 8/6/2018
 * Time: 8:46 AM
 */

namespace GivePay\Gateway\Transactions;

class Address {
    /**
     * @var string Billing address line 1
     */
    private $address_line_1;

    /**
     * @var string Billing address line 2
     */
    private $address_line_2;

    /**
     * @var string Billing city
     */
    private $city;

    /**
     * @var string Billing state
     */
    private $state;

    /**
     * @var string Billind postal code
     */
    private $postal_code;

    /**
     * Address constructor.
     * @param string $address_line_1
     * @param string $address_line_2
     * @param string $city
     * @param string $state
     * @param string $postal_code
     */
    public function __construct($address_line_1, $address_line_2, $city, $state, $postal_code) {
        $this->address_line_1 = $address_line_1;
        $this->address_line_2 = $address_line_2;
        $this->city = $city;
        $this->state = $state;
        $this->postal_code = $postal_code;
    }

    /**
     * @return array Serializes the Address into a address request object for GPG
     */
    public function serialize() {
        return array(
            'line_1'      => $this->address_line_1,
            'line_2'      => $this->address_line_2,
            'city'        => $this->city,
            'state'       => $this->state,
            'postal_code' => $this->postal_code
        );
    }
}