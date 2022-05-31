<?php

namespace Pledg\PledgPaymentGateway\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Model\Method\Adapter;
use Magento\Quote\Api\Data\CartInterface;
use Pledg\PledgPaymentGateway\Model\Ui\ConfigProvider;

class PaymentMethodAvailable implements ObserverInterface
{
    /**
     * payment_method_is_active event handler.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var CartInterface $quote */
        $quote = $observer->getData('quote');
        if ($quote === null) {
            return;
        }

        /** @var DataObject $checkResult */
        $checkResult = $observer->getData('result');
        /** @var Adapter $adapter */
        $adapter = $observer->getData('method_instance');

        if (!in_array($adapter->getCode(), ConfigProvider::getPaymentMethodCodes())) {
            return;
        }

        if (!$adapter->getConfigData('active', $quote->getStoreId())) {
            $checkResult->setData('is_available', false);
        }
    }
}
