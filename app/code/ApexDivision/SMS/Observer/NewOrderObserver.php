<?php

namespace ApexDivision\SMS\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use \Magento\Framework\HTTP\Client\Curl;


class NewOrderObserver implements ObserverInterface
{

    protected $logger;
    protected $_curl;

    public function __construct(
        LoggerInterface $logger,
        \Magento\Framework\HTTP\Client\Curl $curl
    )
    {
        $this->_curl = $curl;
        $this->logger = $logger;

        $this->_curl->setOption(CURLOPT_SSL_VERIFYHOST,false);
        $this->_curl->setOption(CURLOPT_SSL_VERIFYPEER,false);
    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();

//            $oobj = $order->getData();

            $orderId = $order->getIncrementId();
//            $orderId = $order->getOrderId();
//            $orderId = $order->getId();

            $cusomerFirstName = $order->getCustomerFirstname();
            $totalPrice = number_format($order->getGrandTotal(), 2);

            $destination  = $order->getBillingAddress()->getTelephone();

            $msg = "Dear " . $cusomerFirstName . ", Your order has been placed with Order ID " . $orderId . " with TJUK4Home amounting to Rs. " . $totalPrice . " We will send you an update when your order status changes";

            $url = "https://api.smslane.com/vendorsms/pushsms.aspx?user=hemaljal@gmail.com&password=JAL@20021978&msisdn=" . $destination . "&sid=TJUKTN&msg=" . rawurlencode($msg) . "&fl=0&gwid=2";

            $this->logger->info("SMS Data sent: " . $url);

            $this->_curl->get($url);
            $response = $this->_curl->getBody();

            $this->logger->info("SMS Response: " . $response);

        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        };

    }
}
