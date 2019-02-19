<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;
use PayPal\Validation\NumericValidator;
use PayPal\Converter\FormatConverter;

/**
 * Class Amount
 *
 * Let's you specify details of a payment amount.
 *
 * @package PayPal\Api
 *
 * @property string currency
 * @property string total
 * @property \PayPal\Api\Details details
 */
class Amount extends PayPalModel
{
    /**
     * 3 letter currency code
     * 
     *
     * @param string $currency
     * 
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * 3 letter currency code
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Total amount charged from the Payer account (or card) to Payee. In case of a refund, this is the refunded amount to the original Payer from Payee account.
     * 
     *
     * @param string|double $total
     * 
     * @return $this
     */
    public function setTotal($total)
    {
        NumericValidator::validate($total, "Total");
        $total = FormatConverter::formatToPrice($total, $this->getCurrency());
        $this->total = $total;
        return $this;
    }

    /**
     * Total amount charged from the Payer account (or card) to Payee. In case of a refund, this is the refunded amount to the original Payer from Payee account.
     *
     * @return string
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Additional details of the payment amount.
     * 
     *
     * @param \PayPal\Api\Details $details
     * 
     * @return $this
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * Additional details of the payment amount.
     *
     * @return \PayPal\Api\Details
     */
    public function getDetails()
    {
        return $this->details;
    }

}
