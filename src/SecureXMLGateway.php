<?php

namespace Omnipay\IPpay;

use Omnipay\Common\AbstractGateway;

/**
 * IPpay Secure XML Gateway
 *
 * Example:
 *
 * <code>
 *   // Initialise the test gateway
 *   $gateway = \Omnipay\Omnipay::create('IPpay_SecureXML');
 *   $gateway->setMerchantId('ABC0001');
 *   $gateway->setTransactionPassword('abc123');
 *   $gateway->setTestMode(true);
 *
 *   // Create a credit card object
 *   $card = new \Omnipay\Common\CreditCard(
 *       [
 *           'number'      => '4000300020001000',
 *           'expiryMonth' => '6',
 *           'expiryYear'  => '2020',
 *           'cvv'         => '123',
 *       ]
 *   );
 *
 *   // Perform a purchase test
 *   $transaction = $gateway->purchase(
 *       [
 *           'amount'        => '10.00',
 *           'currency'      => 'USD',
 *           'transactionId' => 'invoice_12345',
 *           'card'          => $card,
 *       ]
 *   );
 *
 *   $response = $transaction->send();
 *
 *   if ($response->isSuccessful()) {
 *       echo sprintf('Transaction %s was successful!', $response->getTransactionReference());
 *   } else {
 *       echo sprintf('Transaction %s failed: %s', $response->getTransactionReference(), $response->getMessage());
 *   }
 * </code>
 *
 */
class SecureXMLGateway extends AbstractGateway
{
    public function getName()
    {
        return 'IPpay SecureXML';
    }

    public function getDefaultParameters()
    {
        return [
            'merchantId' => '',
            'transactionPassword' => '',
            'testMode' => false,
        ];
    }

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    public function getTransactionPassword()
    {
        return $this->getParameter('transactionPassword');
    }

    public function setTransactionPassword($value)
    {
        return $this->setParameter('transactionPassword', $value);
    }


    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\IPpay\Message\SecureXMLPurchaseRequest', $parameters);
    }

}
