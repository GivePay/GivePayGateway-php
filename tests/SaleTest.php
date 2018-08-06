<?php
/**
 * Created by PhpStorm.
 * User: WilliamWard
 * Date: 8/6/2018
 * Time: 9:34 AM
 */

use GivePay\Gateway\Transactions\Sale;
use GivePay\Gateway\Transactions\Address;
use GivePay\Gateway\Transactions\Card;
use GivePay\Gateway\Transactions\TerminalType;
use GivePay\Gateway\GivePayGatewayClient;

final class SaleTest extends \PHPUnit\Framework\TestCase {

    public function testSalesCreatesSaleInstance() {
        $this->assertInstanceOf(
            Sale::class,
            new Sale(10, TerminalType::$ECommerce, null, "", "", null)
        );
    }

    public function testSaleSerializesProperly() {
        $card = Card::withCard("12345", "123", "12", "21");
        $address = new Address("line1", "line2", "city", "state", "postal");

        $this->assertSame(
            array(
                'mid'      => "testmid",
                'terminal' => array(
                    'tid'           => 'testtid',
                    'terminal_type' => 'com.givepay.terminal-types.ecommerce'
                ),
                'amount' => array(
                    'base_amount' => 1000.0
                ),
                'payer' => array(
                    'billing_address' => array(
                        "line_1"      => "line1",
                        "line_2"      => "line2",
                        "city"        => "city",
                        "state"       => "state",
                        "postal_code" => "postal"
                    ),
                    'email_address' => 'test@email.com',
                    'phone_number'  => 'phone'
                ),
                'card' => array(
                    "card_number"      => "12345",
                    "card_present"     => false,
                    "expiration_month" => "12",
                    "expiration_year"  => "21",
                    "cvv"              => "123"
                )
            ),
            (new Sale(10, TerminalType::$ECommerce, $address, "test@email.com", "phone", $card))->serialize("testmid", "testtid")
        );
    }

    /**
     * @dataProvider envVarCredsProvider
     */
    public function testCanMakeSale($mid, $tid, $client_id, $client_secret) {
        if (null == $mid) {
            $this->markTestSkipped('no creds found. Skipping test...');
            return;
        }

        $client = new GivePayGatewayClient($client_id, $client_secret, "https://portal.flatratepay-staging.net/connect/token", "https://gpg-stage.flatratepay-staging.net/");
        $sale = new Sale(10, TerminalType::$ECommerce,
            new Address("", "", "", "", "76132"
        ), "email@email.com", "phone",
            Card::withCard("4111111111111111", "123", "12", "21")
        );
        $result = $client->chargeAmount($mid, $tid, $sale);

        $this->assertSame(true, $result->getSuccess());
    }

    public function envVarCredsProvider() {
        return [
            'sale' => [getenv('MID'), getenv('TID'), getenv('CLIENT_ID'), getenv('CLIENT_SECRET')]
        ];
    }
}