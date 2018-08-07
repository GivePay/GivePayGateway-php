<?php
/**
 * Created by PhpStorm.
 * User: WilliamWard
 * Date: 8/6/2018
 * Time: 3:39 PM
 */

use GivePay\Gateway\GivePayGatewayClient;
use GivePay\Gateway\Transactions\Card;
use GivePay\Gateway\Transactions\TerminalType;
use GivePay\Gateway\Transactions\TokenRequest;
use PHPUnit\Framework\TestCase;

final class TokenizationTests extends TestCase
{
    public function testTokenizationRequestCanBeCreated()
    {
        $this->assertInstanceOf(
            TokenRequest::class,
            new TokenRequest(null, "")
        );
    }

    public function testTokenRequestSerializesProperly()
    {
        $this->assertSame(
            array(
                'mid' => 'test mid',
                'terminal' => array(
                    'tid' => 'test tid',
                    'terminal_type' => TerminalType::$ECommerce
                ),
                'card' => array(
                    "card_number" => "12345",
                    "card_present" => false,
                    "expiration_month" => "12",
                    "expiration_year" => "21",
                    "cvv" => "123"
                )
            ),
            (new TokenRequest(
                Card::withCard("12345", "123", "12", "21"),
                TerminalType::$ECommerce
            ))->serialize("test mid", "test tid")
        );
    }

    /**
     * @dataProvider envVarCredsProvider
     */
    public function testCanMakeTokenRequest($mid, $tid, $client_id, $client_secret)
    {
        if (null == $mid) {
            $this->markTestSkipped('no creds found. Skipping test...');
            return;
        }

        $client = new GivePayGatewayClient($client_id, $client_secret, "https://portal.flatratepay-staging.net/connect/token", "https://gpg-stage.flatratepay-staging.net/");
        $result = $client->storeCard($mid, $tid, Card::withCard(
            "4111111111111111",
            '123',
            '12', '20'
        ));

        $this->assertNotNull($result);
    }

    public function envVarCredsProvider()
    {
        return [
            'sale' => [getenv('MID'), getenv('TID'), getenv('CLIENT_ID'), getenv('CLIENT_SECRET')]
        ];
    }
}