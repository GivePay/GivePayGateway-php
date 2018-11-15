# GivePay Gateway PHP

[![Build Status](https://api.travis-ci.org/GivePay/GivePayGateway-php.svg?branch=master)](https://travis-ci.org/GivePay/GivePayGateway-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/GivePay/GivePayGateway-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/GivePay/GivePayGateway-php/?branch=master)[![Code Coverage](https://scrutinizer-ci.com/g/GivePay/GivePayGateway-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/GivePay/GivePayGateway-php/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/GivePay/GivePayGateway-php/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)

A library for interacting with FlatRatePay transactions APIs with the GivePay Gateway.

## Installation

Install the library with [Composer](https://getcomposer.org/):

```bash
composer require givepay/givepay-gateway
```

## Usage

Simple sale transaction example:

```php
<?php

use \GivePay\Gateway\GivePayGatewayClient;
use \GivePay\Gateway\Transactions\Address;
use \GivePay\Gateway\Transactions\Card;
use \GivePay\Gateway\Transactions\Sale;
use \GivePay\Gateway\Transactions\TerminalType;

// Get configuration information
$client_id     = getenv("CLIENT_ID");
$client_secret = getenv("CLIENT_SECRET");
$merchant_id   = getenv("MERCHANT_ID");
$terminal_id   = getenv("TERMINAL_ID");

// Create the credit/debit card object
$card = Card::withCard(
    "411111111111", // Test Visa PAN
    "331", // CVV/CVV2
    "09", // Expiration month (MM)
    "23" // Expiration year (YY)
);

// Collect the payer's billing address 
$payer_address = new Address(
    "1 N Main St.",
    "",
    "Fort Worth",
    "TX",
    "76104"
);

$sale = new Sale(
    17.00, // Transaction amount in dollars
    TerminalType::$ECommerce,
    $payer_address,
    "tester@example.com", // Billing email address
    "5555555555", // Billing phone number
    $card
);

// Create the client. You may use this globally within your application
$client = new GivePayGatewayClient(
    $client_id, $client_secret
);

// Make the transaction
$transaction_result = $client->chargeAmount($merchant_id, $terminal_id, $sale);

if (true == $transaction_result->getSuccess()) {
    echo "Transaction ID: " . $transaction_result->getTransactionId();
} else {
    echo "Transaction failed with message: " . $transaction_result->getErrorMessage();
}

?>
```

Voiding a transaction

```php
<?php

use \GivePay\Gateway\GivePayGatewayClient;
use \GivePay\Gateway\Transactions\V0id;
use \GivePay\Gateway\Transactions\TerminalType;

/**
* @var GivePayGatewayClient $client The client
 */
$client;

/**
* @var string $merchant_id Your merchant ID
 */
$merchant_id;

/**
* @var string $terminal_id Your terminal ID
 */
$terminal_id;

// The transaction ID of the transaction to void
$transaction_id = "<transaction id>";

$void = new V0id(
    TerminalType::$ECommerce,
    $transaction_id // The transaction ID
);

// Make the void transaction
$void_result = $client->voidTransaction(
    $transaction_id, 
    $merchant_id, 
    $terminal_id
);

if (true == $void_result->getSuccess()) {
    echo "Transaction ID: " . $void_result->getTransactionId();
} else {
    echo "Transaction failed with message: " . $void_result->getErrorMessage();
}

```

Creating and using tokens:

```php
<?php

use \GivePay\Gateway\GivePayGatewayClient;
use \GivePay\Gateway\Transactions\Address;
use \GivePay\Gateway\Transactions\Card;
use \GivePay\Gateway\Transactions\Sale;
use \GivePay\Gateway\Transactions\TerminalType;

/**
* @var GivePayGatewayClient $client The client
 */
$client;

/**
* @var string $merchant_id Your merchant ID
 */
$merchant_id;

/**
* @var string $terminal_id Your terminal ID
 */
$terminal_id;

/**
* @var Address $payer_address The payer's billing address
 */
$payer_address;

// Create the credit/debit card object
$card_to_store = Card::withCard(
    "411111111111", // Test Visa PAN
    "331", // CVV/CVV2
    "09", // Expiration month (MM)
    "23" // Expiration year (YY)
);

// Store the card in the gateway and retrieve a payment token string
$token = $client->storeCard(
    $merchant_id,
    $terminal_id,
    $card_to_store
);

// Create a card from a token string
$payment_card = Card::withToken($token);

// Create the Sale request
$sale = new Sale(
    17.50, 
    TerminalType::$ECommerce, 
    $payer_address, 
    "tester@example.com", 
    "5555555555", 
    $payment_card
);

// Make the transaction
$sale_result = $client->chargeAmount(
    $merchant_id,
    $terminal_id,
    $sale
);

if (true == $sale_result->getSuccess()) {
    echo "Transaction ID: " . $sale_result->getTransactionId();
} else {
    echo "Transaction failed with message: " . $sale_result->getErrorMessage();
}

```

## License

`givepay/givepay-gateway` is licensed under the GPLv3 license.