<?php
/**
 * Created by PhpStorm.
 * User: WilliamWard
 * Date: 8/6/2018
 * Time: 10:15 AM
 */

use \PHPUnit\Framework\TestCase;
use \givepay\FlatRatePay\Transactions\Address;

final class AddressTest extends TestCase {

    public function testAddressCanBeCreated() {
        $this->assertInstanceOf(
            Address::class,
            new Address("line1", "", "", "", "")
        );
    }

    public function testAddressSerializesProperly() {
        $this->assertSame(
            array(
                "line_1"      => "line1",
                "line_2"      => "line2",
                "city"        => "city",
                "state"       => "state",
                "postal_code" => "postal"
            ),
            (new Address("line1", "line2", "city", "state", "postal"))->serialize()
        );
    }
}