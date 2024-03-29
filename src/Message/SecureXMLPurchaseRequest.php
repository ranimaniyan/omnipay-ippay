<?php

namespace Omnipay\IPpay\Message;

/**
 * IPpay SecureXML Purchase Request.
 */
class SecureXMLPurchaseRequest extends SecureXMLAbstractRequest
{
    protected $txnType = 0; // Standard Payment, as per Appendix A of documentation.
    protected $requiredFields = ['amount', 'card', 'transactionId'];

    public function getData()
    {
        return $this->getBasePaymentXMLWithCard();
    }
}
