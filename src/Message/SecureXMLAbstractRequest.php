<?php

namespace Omnipay\IPpay\Message;

/**
 * IPpay SecureXML Abstract Request.
 */
abstract class SecureXMLAbstractRequest extends AbstractRequest
{
    /**
     * @var string
     */
    public $testEndpoint = 'https://testgtwy.ippay.com/ippay';
    //public $testEndpoint = 'https://staging.ippay.com/ippaygateway/PaymentProcess.aspx ';



    /**
     * @var string
     */
    public $liveEndpoint = 'https://hostedpage.ippay.com/';

    /**
     * @var string
     */
    protected $requestType = 'Payment';

    /**
     * @var array
     */
    protected $requiredFields = [];

    /**
     * Set the messageID on the request.
     *
     * This is returned intact on any response so you could add a local
     * database ID here to ease in matching data later.
     */
    public function setMessageId($value)
    {
        return $this->setParameter('messageId', $value);
    }

    /**
     * Get the messageID for the request.
     *
     * @return string User-supplied messageID or generated one based on
     *                timestamp.
     */
    public function getMessageId()
    {
        $messageId = $this->getParameter('messageId');

        if (empty($messageId)) {
            $this->setMessageId(substr(md5(microtime()), 0, 30));
        }

        return $this->getParameter('messageId');
    }

    public function sendData($data)
    {
        $httpResponse = $this->httpClient->request('POST', $this->getEndpoint(), [], $data->asXML());

        $xml = new \SimpleXMLElement($httpResponse->getBody()->getContents());

        return $this->response = new SecureXMLResponse($this, $xml);
    }

    /**
     * XML Template of a IPpayMessage.
     *
     * As per section 5.1 of the documentation, these elements are common to
     * all requests.
     *
     * @return \SimpleXMLElement IPpayMessage template.
     */
    protected function getBaseXML()
    {
        foreach ($this->requiredFields as $field) {
            $this->validate($field);
        }

        $xml = new \SimpleXMLElement('<SecurePayMessage/>');

        $messageInfo = $xml->addChild('MessageInfo');
        $messageInfo->messageID = $this->getMessageId();
        $messageInfo->addChild('messageTimestamp', $this->generateTimestamp());
        $messageInfo->addChild('timeoutValue', 60);
        $messageInfo->addChild('apiVersion', 'xml-4.2');

        $merchantInfo = $xml->addChild('MerchantInfo');
        $merchantInfo->addChild('merchantID', $this->getMerchantId());
        $merchantInfo->addChild('password', $this->getTransactionPassword());

        $xml->addChild('RequestType', $this->requestType); // Not related to the transaction type

        return $xml;
    }

    /**
     * XML template of a IPpayMessage Payment.
     *
     * @return \SimpleXMLElement IPpayMessage with transaction details.
     */
    protected function getBasePaymentXML()
    {
        $xml = $this->getBaseXML();

        $payment = $xml->addChild('Payment');
        $txnList = $payment->addChild('TxnList');
        $txnList->addAttribute('count', 1); // One transaction per request supported by current API.

        $transaction = $txnList->addChild('Txn');
        $transaction->addAttribute('ID', 1); // One transaction per request supported by current API.
        $transaction->addChild('txnType', $this->txnType);
        $transaction->addChild('txnSource', 23); // Must always be 23 for SecureXML.
        $transaction->addChild('amount', $this->getAmountInteger());
        $transaction->addChild('currency', $this->getCurrency());
        $transaction->purchaseOrderNo = $this->getTransactionId();

        return $xml;
    }

    /**
     * @return \SimpleXMLElement IPpayMessage with transaction and card
     * details.
     */
    protected function getBasePaymentXMLWithCard()
    {
        $this->getCard()->validate();

        $xml = $this->getBasePaymentXML();

        $card = $xml->Payment->TxnList->Txn->addChild('CreditCardInfo');
        $card->addChild('cardNumber', $this->getCard()->getNumber());
        $card->addChild('cvv', $this->getCard()->getCvv());
        $card->addChild('expiryDate', $this->getCard()->getExpiryDate('m/y'));

        return $xml;
    }

    /**
     * Generates a SecureXML timestamp.
     *
     * SecureXML requires a specific timestamp format as per appendix E of the
     * documentation.
     *
     * @return string SecureXML formatted timestamp.
     */
    protected function generateTimestamp()
    {
        $date = new \DateTime();

        // API requires the timezone offset in minutes
        return $date->format(sprintf('YmdHis000%+04d', $date->format('Z') / 60));
    }
}
