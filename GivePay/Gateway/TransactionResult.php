<?php
/**
 * Created by PhpStorm.
 * User: WilliamWard
 * Date: 8/6/2018
 * Time: 10:49 AM
 */

namespace GivePay\Gateway;

final class TransactionResult
{
    private $success;
    private $transaction_id;
    private $error_message;
    private $code;

    public function __construct($success, $transaction_id = null, $error_message = null, $code = null)
    {
        $this->success = $success;
        $this->transaction_id = $transaction_id;
        $this->error_message = $error_message;
        $this->code = $code;
    }

    /**
     * @return bool
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}