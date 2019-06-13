<?php
/**
 * Created by PhpStorm.
 * User: WilliamWard
 * Date: 8/6/2018
 * Time: 3:24 PM
 */

use GivePay\Gateway\GivePayGatewayClient;
use GivePay\Gateway\Transactions\TerminalType;
use GivePay\Gateway\Transactions\V0id;
use PHPUnit\Framework\TestCase;

final class VoidTest extends TestCase
{
    public function testVoidCanBeCreated()
    {
        $this->assertInstanceOf(
            V0id::class,
            new V0id("", "")
        );
    }

    public function testVoidSerializesProperly()
    {
        $this->assertSame(
            array(
                'mid' => 'test mid',
                'terminal' => array(
                    'tid' => 'test tid',
                    'terminal_type' => TerminalType::$ECommerce,
                ),
                'transaction_id' => 'tran ID'
            ),
            (new V0id(TerminalType::$ECommerce, 'tran ID'))->serialize('test mid', 'test tid')
        );
    }

    /**
     * @dataProvider envVarCredsProvider
     */
    public function testCanMakeVoid($mid, $tid, $client_id, $client_secret, $transaction_id)
    {
        if (null == $mid) {
            $this->markTestSkipped('no creds found. Skipping test...');
            return;
        }

        $client = new GivePayGatewayClient($client_id, $client_secret, "https://portal.flatratepay-staging.net/connect/token", "https://gpg-stage.flatratepay-staging.net/");
        $result = $client->voidTransaction($transaction_id, $mid, $tid);

        $this->assertSame(true, $result->getSuccess());
    }

    public function envVarCredsProvider()
    {
        return [
            'jp' => [getenv('MID'), getenv('TID'), getenv('CLIENT_ID'), getenv('CLIENT_SECRET'), getenv('TRANSACTION_ID')],
            'cc' => [getenv('MID'), getenv('CCTID'), getenv('CLIENT_ID'), getenv('CLIENT_SECRET'), getenv('TRANSACTION_ID')]
        ];
    }
}