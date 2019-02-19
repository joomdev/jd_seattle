<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class PaymentExecution
 *
 * Let's you execute a PayPal Account based Payment resource with the payer_id obtained from web approval url.
 *
 * @package PayPal\Api
 *
 * @property string payer_id
 * @property \PayPal\Api\Transactions transactions
 */
class PaymentExecution extends PayPalModel
{
    /**
     * PayPal assigned Payer ID returned in the approval return url.
     * 
     *
     * @param string $payer_id
     * 
     * @return $this
     */
    public function setPayerId($payer_id)
    {
        $this->payer_id = $payer_id;
        return $this;
    }

    /**
     * PayPal assigned Payer ID returned in the approval return url.
     *
     * @return string
     */
    public function getPayerId()
    {
        return $this->payer_id;
    }

    /**
     * If the amount needs to be updated after obtaining the PayPal Payer info (eg. shipping address), it can be updated using this element.
     * 
     *
     * @param \PayPal\Api\Transactions $transactions
     * 
     * @return $this
     */
    public function setTransactions($transactions)
    {
        $this->transactions = $transactions;
        return $this;
    }

    /**
     * If the amount needs to be updated after obtaining the PayPal Payer info (eg. shipping address), it can be updated using this element.
     *
     * @return \PayPal\Api\Transactions[]
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

}
