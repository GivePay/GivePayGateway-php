<?php
/**
 * Created by PhpStorm.
 * User: WilliamWard
 * Date: 8/6/2018
 * Time: 8:44 AM
 */

namespace GivePay\Gateway\Transactions;

final class Card
{

    /**
     * @var string The PAN for the card
     */
    private $pan;

    /**
     * @var string The card holder's CVV/CVV2
     */
    private $cvv;

    /**
     * @var string The card's expiration month (MM)
     */
    private $expiration_month;

    /**
     * @var string The card's expiration year (YY)
     */
    private $expiration_year;

    /**
     * @var string The token
     */
    private $token;

    /**
     * @var bool Whether or not this card should be treated as a token
     */
    private $is_token_card = false;

    /**
     * Creates a card from information from a physical card
     * @param string $pan
     * @param string $cvv
     * @param string $expiration_month
     * @param string $expiration_year
     *
     * @return Card
     */
    public static function withCard($pan, $cvv, $expiration_month, $expiration_year)
    {
        $card = new self();
        $card->pan = $pan;
        $card->cvv = $cvv;
        $card->expiration_month = $expiration_month;
        $card->expiration_year = $expiration_year;

        return $card;
    }

    /**
     * Creates a card from a payment token
     * @param string $token The payment card token
     * @param string $cvv The optional cvc/cvv2 value for the card
     * @return Card
     */
    public static function withToken($token, $cvv = null)
    {
        $card = new self();
        $card->token = $token;
        $card->cvv = $cvv;
        $card->is_token_card = true;

        return $card;
    }

    /**
     * @return array Serializes the card into a GPG request param
     */
    public function serialize()
    {
        if ($this->isTokenCard()) {
            return array(
                'token' => $this->token,
                'cvv' => $this->cvv
            );
        }

        return array(
            'card_number' => $this->pan,
            'card_present' => false,
            'expiration_month' => $this->expiration_month,
            'expiration_year' => $this->expiration_year,
            'cvv' => $this->cvv
        );
    }

    /**
     * @return bool Whether or not this card is tokenized
     */
    private function isTokenCard()
    {
        return $this->is_token_card;
    }
}