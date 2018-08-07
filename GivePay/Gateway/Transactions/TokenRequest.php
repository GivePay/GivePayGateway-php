<?php
/**
 * Created by PhpStorm.
 * User: WilliamWard
 * Date: 8/6/2018
 * Time: 3:35 PM
 */

namespace GivePay\Gateway\Transactions;

final class TokenRequest
{
    /**
     * @var Card card
     */
    private $card;

    /**
     * @var string Terminal Type
     */
    private $terminal_type;

    /**
     * TokenRequest constructor.
     * @param Card $card
     * @param string $terminal_type
     */
    public function __construct($card, $terminal_type)
    {
        $this->card = $card;
        $this->terminal_type = $terminal_type;
    }

    /**
     * Serializes the tokenization request into a GPG request
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
            'card' => $this->getCard()->serialize()
        );
    }

    /**
     * @return string
     */
    public function getTerminalType()
    {
        return $this->terminal_type;
    }

    /**
     * @return \GivePay\Gateway\Transactions\Card
     */
    public function getCard()
    {
        return $this->card;
    }
}