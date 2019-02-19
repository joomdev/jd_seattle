<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class Payer
 *
 * A resource representing a Payer that funds a payment.
 *
 * @package PayPal\Api
 *
 * @property string payment_method
 * @property string status
 * @property \PayPal\Api\FundingInstrument[] funding_instruments
 * @property string funding_option_id
 * @property \PayPal\Api\PayerInfo payer_info
 */
class Payer extends PayPalModel
{
    /**
     * Payment method being used - PayPal Wallet payment, Bank Direct Debit, or Direct Credit card.
     * Valid Values: ["credit_card", "bank", "paypal"]
     *
     * @param string $payment_method
     * 
     * @return $this
     */
    public function setPaymentMethod($payment_method)
    {
        $this->payment_method = $payment_method;
        return $this;
    }

    /**
     * Payment method being used - PayPal Wallet payment, Bank Direct Debit, or Direct Credit card.
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    /**
     * Status of Payer PayPal Account.
     * Valid Values: ["VERIFIED", "UNVERIFIED"]
     *
     * @param string $status
     * 
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Status of Payer PayPal Account.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * List of funding instruments from which the funds of the current payment come. Typically a credit card.
     *
     * @param \PayPal\Api\FundingInstrument[] $funding_instruments
     * 
     * @return $this
     */
    public function setFundingInstruments($funding_instruments)
    {
        $this->funding_instruments = $funding_instruments;
        return $this;
    }

    /**
     * List of funding instruments from which the funds of the current payment come. Typically a credit card.
     *
     * @return \PayPal\Api\FundingInstrument[]
     */
    public function getFundingInstruments()
    {
        return $this->funding_instruments;
    }

    /**
     * Append FundingInstruments to the list.
     *
     * @param \PayPal\Api\FundingInstrument $fundingInstrument
     * @return $this
     */
    public function addFundingInstrument($fundingInstrument)
    {
        if (!$this->getFundingInstruments()) {
            return $this->setFundingInstruments(array($fundingInstrument));
        } else {
            return $this->setFundingInstruments(
                array_merge($this->getFundingInstruments(), array($fundingInstrument))
            );
        }
    }

    /**
     * Remove FundingInstruments from the list.
     *
     * @param \PayPal\Api\FundingInstrument $fundingInstrument
     * @return $this
     */
    public function removeFundingInstrument($fundingInstrument)
    {
        return $this->setFundingInstruments(
            array_diff($this->getFundingInstruments(), array($fundingInstrument))
        );
    }

    /**
     * Id of user selected funding option for the payment. 'OneOf' funding_instruments or funding_option_id to be present.
     *
     * @param string $funding_option_id
     * 
     * @return $this
     */
    public function setFundingOptionId($funding_option_id)
    {
        $this->funding_option_id = $funding_option_id;
        return $this;
    }

    /**
     * Id of user selected funding option for the payment. 'OneOf' funding_instruments or funding_option_id to be present.
     *
     * @return string
     */
    public function getFundingOptionId()
    {
        return $this->funding_option_id;
    }

    /**
     * Information related to the Payer. 
     *
     * @param \PayPal\Api\PayerInfo $payer_info
     * 
     * @return $this
     */
    public function setPayerInfo($payer_info)
    {
        $this->payer_info = $payer_info;
        return $this;
    }

    /**
     * Information related to the Payer. 
     *
     * @return \PayPal\Api\PayerInfo
     */
    public function getPayerInfo()
    {
        return $this->payer_info;
    }

}
