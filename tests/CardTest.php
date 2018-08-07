<?php
/**
 * Created by PhpStorm.
 * User: WilliamWard
 * Date: 8/6/2018
 * Time: 9:35 AM
 */

use GivePay\Gateway\Transactions\Card;
use PHPUnit\Framework\TestCase;

final class CardTest extends TestCase
{
    public function testCardCanBeCreatedWithCardInfo()
    {
        $this->assertInstanceOf(
            Card::class,
            Card::withCard("test pan", "123", "12", "20")
        );
    }

    public function testCardCanBeCreatedWithTokenInfo()
    {
        $this->assertInstanceOf(
            Card::class,
            Card::withToken("tokentest")
        );
    }

    public function testCardFromCardInfoSerializesProperly()
    {
        $this->assertSame(
            array(
                "card_number" => "12345",
                "card_present" => false,
                "expiration_month" => "12",
                "expiration_year" => "21",
                "cvv" => "123"
            ),
            Card::withCard("12345", "123", "12", "21")->serialize()
        );
    }

    public function testCardFromTokenSerializesProperly()
    {
        $this->assertContains(
            "test token",
            Card::withToken("test token")->serialize()
        );
    }
}