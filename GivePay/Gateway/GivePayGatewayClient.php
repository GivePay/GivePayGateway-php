<?php

namespace GivePay\Gateway;

use cURL\Request;
use \Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class GivePayGatewayClient {

	public $token_endpoint;

	public $gateway_url;

	private $client_secret;

	private $client_id;

	private $logger;

	/**
	 * FRP_Gateway constructor.
	 *
	 * @param string $client_id
	 * @param string $client_secret
	 * @param string $token_endpoint
	 * @param string $gateway_url
     * @param LoggerInterface $logger
	 */
	public function __construct(
		$client_id,
		$client_secret,
		$token_endpoint = 'https://portal.flatratepay.com/connect/token',
		$gateway_url = 'https://gateway.givepaycommerce.com',
        $logger = null
	) {
		$this->token_endpoint = $token_endpoint;
		$this->gateway_url    = $gateway_url;

		$this->client_secret = $client_secret;
		$this->client_id     = $client_id;

		if ($logger == null) {
		    $this->logger = new NullLogger();
        } else {
		    $this->logger = $logger;
		}
	}

	/**
	 * @param string $merchant_id The merchant ID for the website
	 * @param string $terminal_id The terminal ID for the website
	 * @param Transactions\Sale $sale The transaction information
	 *
	 * @throws \Exception
	 * @return TransactionResult
	 */
	public function chargeAmount( $merchant_id, $terminal_id, $sale ) {
		if ( null == $sale ) {
			throw new \Exception( '$sale is null' );
		}

		$access_token = $this->getAccessToken( $this->client_id, $this->client_secret, $this->token_endpoint );
		if ( null == $access_token ) {
			throw new \Exception( 'Could not authorize with gateway.' );
		}

		return $this->makeSaleRequest( $access_token, $merchant_id, $terminal_id, $sale );
	}

	/**
	 * @param string $transaction_id
	 * @param string $merchant_id
	 * @param string $terminal_id
	 *
	 * @throws Exception
	 * @return TransactionResult
	 */
	public function voidTransaction( $transaction_id, $merchant_id, $terminal_id ) {
		if ( null == $transaction_id ) {
			throw new Exception( 'Transaction ID is null' );
		}

		$access_token = $this->getAccessToken( $this->client_id, $this->client_secret, $this->token_endpoint );
		if ( null == $access_token ) {
			throw new Exception( 'Could not authorize with gateway.' );
		}

		return $this->makeVoidRequest( $access_token, $transaction_id, $merchant_id, $terminal_id );
	}

	/**
	 * Stores the card and gets a token from the gateway
	 *
	 * @param string $merchant_id
	 * @param string $terminal_id
	 * @param array $card
	 *
	 * @return string
	 * @throws Exception
	 */
	public function storeCard( $merchant_id, $terminal_id, $card ) {
		if ( null == $card ) {
			throw new Exception( 'Card is null' );
		}

		$access_token = $this->getAccessToken( $this->client_id, $this->client_secret, $this->token_endpoint );
		if ( null == $access_token ) {
			throw new Exception( 'Could not store card with gateway.' );
		}

		return $this->makeStoreCardRequest( $access_token, $merchant_id, $terminal_id, $card );
	}

	/**
	 * @param string $access_token
	 * @param string $merchant_id
	 * @param string $terminal_id
     * @param Transactions\Sale $sale
	 *
	 * @return TransactionResult
	 */
	private function makeSaleRequest( $access_token, $merchant_id, $terminal_id, $sale ) {
		$sale_request = $sale->serialize($merchant_id, $terminal_id);
		$body = json_encode( $sale_request );

		$this->logger->info("Starting transaction for $" . $sale->getTotal());

		$request = new Request($this->gateway_url . 'api/v1/transactions/sale');
		$request->getOptions()
            ->set(CURLOPT_RETURNTRANSFER, true)
            ->set(CURLOPT_POST, true)
            ->set(CURLOPT_POSTFIELDS, $body)
            ->set(CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body),
                'Accept: application/json',
                'Authorization: Bearer ' . $access_token
            ));
        $response = $request->send();

        $this->logger->debug( "Transaction completed" );

		$sale_response = json_decode( $response->getContent() );

		if ( $sale_response->success ) {
			$transaction_id = $sale_response->result->transaction_id;

            $this->logger->info( 'Payment completed. Transaction ID: ' . $transaction_id );

			return new TransactionResult( true, $transaction_id );
		} else {
			$error_message = $sale_response->error->message;
			$code          = $sale_response->error->code;

            $this->logger->debug( "Sale response: " . var_export( $sale_response, true ) );
            $this->logger->error( "Payment failed." );

			return new TransactionResult( false, null, $error_message, $code );
		}
	}

	/**
	 * Makes a VOID request
	 *
	 * @param string $access_token
	 * @param string $transaction_id
	 * @param string $merchant_id
	 * @param string $terminal_id
	 *
	 * @return TransactionResult
	 */
	private function makeVoidRequest( $access_token, $transaction_id, $merchant_id, $terminal_id ) {
		$void_request = $this->generateVoidRequest( $merchant_id, $terminal_id, $transaction_id );

		$body = json_encode( $void_request );

        $this->logger->info( "Starting void transaction for transaction# " . $transaction_id );

        $request = new Request($this->gateway_url . 'api/v1/transactions/void');
        $request->getOptions()
            ->set(CURLOPT_RETURNTRANSFER, true)
            ->set(CURLOPT_POST, true)
            ->set(CURLOPT_POSTFIELDS, $body)
            ->set(CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body),
                'Accept: application/json',
                'Authorization: Bearer ' . $access_token
            ));
        $response = $request->send();

        $this->logger->debug( "Transaction completed" );

		$void_response = json_decode( $response->getContent() );

		if ( $void_response->success ) {
			$transaction_id = $void_response->result->transaction_id;

            $this->logger->info( 'Void completed. Transaction ID: ' . $transaction_id );

			return new TransactionResult( true, $transaction_id );
		} else {
			$error_message = $void_response->error->message;
			$code          = $void_response->error->code;

            $this->logger->debug( "Void response: " . var_export( $void_response, true ) );
            $this->logger->error( "Void failed." );

			return new TransactionResult( false, null, $error_message, $code );
		}
	}

	/**
	 * stores a card in the gateway
	 *
	 * @param string $access_token
	 * @param string $terminal_id
	 * @param string $merchant_id
	 * @param mixed $card
	 *
	 * @return string
	 */
	private function makeStoreCardRequest( $access_token, $merchant_id, $terminal_id, $card ) {
		$token_request = $this->generateTokenizationRequest( $merchant_id, $terminal_id, $card );

		$body = json_encode( $token_request );

        $this->logger->info( "Starting request for tokenization" );

        $request = new Request($this->gateway_url . 'api/v1/transactions/tokenize');
        $request->getOptions()
            ->set(CURLOPT_RETURNTRANSFER, true)
            ->set(CURLOPT_POST, true)
            ->set(CURLOPT_POSTFIELDS, $body)
            ->set(CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body),
                'Accept: application/json',
                'Authorization: Bearer ' . $access_token
            ));
        $response = $request->send();

        $this->logger->debug( "Transaction completed" );

        $token_response = json_decode( $response->getContent() );

		if ( $token_response->success ) {
			$transaction_id = $token_response->result->transaction_id;

            $this->logger->info( 'Tokenization completed. Transaction ID: ' . $transaction_id );

			return $token_response->result->token;
		} else {
            $this->logger->debug( "Tokenization response: " . var_export( $token_response, true ) );
            $this->logger->error( "Tokenization failed." );

			return '';
		}
	}

	/**
	 * Gets an access token from the auth server
	 *
	 * @param string $client_id the client ID
	 * @param string $client_secret the client secret
	 * @param string $token_url the token endpoint
	 *
	 * @return string
	 */
	private function getAccessToken( $client_id, $client_secret, $token_url ) {
		$token_data = array(
			'client_id'     => $client_id,
			'grant_type'    => 'client_credentials',
			'client_secret' => $client_secret,
			'scope'         => 'authorize:transactions capture:transactions sale:transactions refund:transactions void:transactions tokenize:transactions'
		);

        $request = new Request($token_url);
        $request->getOptions()
            ->set(CURLOPT_RETURNTRANSFER, true)
            ->set(CURLOPT_POST, true)
            ->set(CURLOPT_POSTFIELDS, $token_data)
            ->set(CURLOPT_HTTPHEADER, array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept: application/json'
            ));
        $response = $request->send();

		if ( $response->hasError() ) {
            $this->logger->debug( 'Token response status code was ' . $response->getError()->getCode() );
            $this->logger->debug( "Token request ended in failure: " . var_export( $response, true ) );
            $this->logger->error( "Gateway authorization failed. Check credentials." );

			return null;
		}

        $this->logger->debug( "Token request was a success: " . $response->getContent());
		$token = json_decode( $response->getContent() );

		return $token->access_token;
	}

	/**
	 * Generate a void request
	 *
	 * @param string $merchant_id
	 * @param string $terminal_id
	 * @param string $transaction_id
	 *
	 * @return array
	 **/
	private function generateVoidRequest( $merchant_id, $terminal_id, $transaction_id ) {
		$refund_request = array(
			'mid'            => $merchant_id,
			'terminal'       => array(
				'tid'           => $terminal_id,
				'terminal_type' => 'com.givepay.terminal-types.ecommerce'
			),
			'transaction_id' => $transaction_id
		);

		return $refund_request;
	}

	/**
	 * Generates a tokenization request
	 *
	 * @param string $merchant_id
	 * @param string $terminal_id
	 * @param array $card
	 *
	 * @return array
	 */
	private function generateTokenizationRequest( $merchant_id, $terminal_id, $card ) {
		$token_request = array(
			'mid'      => $merchant_id,
			'terminal' => array(
				'tid'           => $terminal_id,
				'terminal_type' => 'com.givepay.terminal-types.ecommerce'
			),
			'card'     => array(
				'card_number'      => $card['card_number'],
				'card_present'     => false,
				'expiration_month' => $card['expiration_month'],
				'expiration_year'  => $card['expiration_year'],
				'cvv'              => $card['cvv']
			)
		);

		return $token_request;
	}
}