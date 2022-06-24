<?php

namespace Pledg\PledgPaymentGateway\Block\Checkout;

use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Pledg\PledgPaymentGateway\Helper\Config;
use Pledg\PledgPaymentGateway\Helper\Crypto;

class Pay extends Template
{
    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var Crypto
     */
    private $crypto;

    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var Order
     */
    private $order;

    /**
     * @param Template\Context   $context
     * @param Config             $configHelper
     * @param Crypto             $crypto
     * @param CollectionFactory  $orderCollectionFactory
     * @param CustomerRepository $customerRepository
     * @param array              $data
     */
    public function __construct(
        Template\Context $context,
        Config $configHelper,
        Crypto $crypto,
        CollectionFactory $orderCollectionFactory,
        CustomerRepository $customerRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->configHelper = $configHelper;
        $this->crypto = $crypto;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return array
     */
    public function getPledgData(): array
    {
        /** @var Order $order */
        $order = $this->getOrder();
        $orderIncrementId = $order->getIncrementId();
        $orderAddress = $order->getBillingAddress();

        $pledgData = [
            'merchantUid' => $this->configHelper->getMerchantIdForOrder($order),
            'amountCents' => round($order->getGrandTotal() * 100),
            'email' => $order->getCustomerEmail(),
            'title' => 'Order ' . $orderIncrementId,
            'reference' => Config::ORDER_REFERENCE_PREFIX . $orderIncrementId,
            'firstName' => $orderAddress->getFirstname(),
            'lastName' => $orderAddress->getLastname(),
            'currency' => $order->getOrderCurrencyCode(),
            'lang' => $this->getLang(),
            'countryCode' => $orderAddress->getCountryId(),
            'address' => $this->getAddressData($orderAddress),
            'metadata' => $this->getMetaData($order),
            'showCloseButton' => true,
            'paymentNotificationUrl' => $this->getUrl('django/checkout/ipn', [
                '_secure' => true,
                'ipn_store_id' => $order->getStoreId(),
                'pledg_method' => $order->getPayment()->getMethod(),
            ]),
        ];

        if (!$order->getIsVirtual()) {
            $pledgData['shipping_address'] = $this->getAddressData($order->getShippingAddress());
        }

        $telephone = $orderAddress->getTelephone();
        if (!empty($telephone)) {
            $pledgData['phoneNumber'] = preg_replace('/^(\+|00)(.*)$/', '$2', $telephone);
        }

        $secretKey = $order->getPayment()->getMethodInstance()->getConfigData('secret_key', $order->getStoreId());
        if (empty($secretKey)) {
            return $this->encodeData($pledgData);
        }

        return [
            'signature' => $this->crypto->encode(['data' => $pledgData], $secretKey),
        ];
    }

    /**
     * @return string
     */
    private function getLang(): string
    {
        $lang = $this->_scopeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORES);

        $allowedLangs = [
            'fr_FR',
            'de_DE',
            'en_GB',
            'es_ES',
            'it_IT',
            'nl_NL',
        ];

        if (in_array($lang, $allowedLangs)) {
            return $lang;
        }

        return reset($allowedLangs);
    }

    /**
     * @param OrderAddressInterface $orderAddress
     *
     * @return array
     */
    private function getAddressData(OrderAddressInterface $orderAddress): array
    {
        return [
            'street' => is_array($orderAddress->getStreet()) ?
                implode(' ', $orderAddress->getStreet()) : $orderAddress->getStreet(),
            'city' => $orderAddress->getCity(),
            'zipcode' => (string)$orderAddress->getPostcode(),
            'stateProvince' => (string)$orderAddress->getRegion(),
            'country' => $orderAddress->getCountryId(),
        ];
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    private function getMetaData(Order $order): array
    {
        $physicalProductTypes = [
            'simple',
            'configurable',
            'bundle',
            'grouped',
        ];

        $products = [];
        /** @var Order\Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            $productType = $item->getProductType();
            $products[] = [
                'reference' => $item->getSku(),
                'type' => in_array($productType, $physicalProductTypes) ? 'physical' : 'virtual',
                'quantity' => (int)$item->getQtyOrdered(),
                'name' => $item->getName(),
                'unit_amount_cents' => round($item->getPriceInclTax() * 100),
            ];
            if (count($products) === 5) {
                // Metadata field is limited in size
                // Include max 5 products information
                break;
            }
        }

        return array_merge([
            'plugin' => sprintf(
                'magento%s-pledg-plugin%s',
                $this->configHelper->getMagentoVersion(),
                $this->configHelper->getModuleVersion()
            ),
            'products' => $products,
        ], $this->getCustomerData($order));
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    private function getCustomerData(Order $order): array
    {
        $customerId = (int)$order->getCustomerId();
        if (empty($customerId)) {
            return [];
        }

        try {
            $customer = $this->customerRepository->getById($customerId);

            return ['account' => [
                'creation_date' => (new \DateTime($customer->getCreatedAt()))->format('Y-m-d'),
                'number_of_purchases' => (int)$this->orderCollectionFactory->create($customerId)->getSize(),
            ]];
        } catch (\Exception $e) {
            $this->_logger->error('Could not resolve order customer for Django data', [
                'exception' => $e,
                'order' => $order->getIncrementId(),
            ]);
        }

        return [];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function encodeData(array $data): array
    {
        $convertedData = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $convertedData[$key] = $this->encodeData($value);
                continue;
            }

            if (mb_check_encoding($value, 'UTF-8') === false) {
                $value = $this->convToUtf8($value);
            }
            $convertedData[$key] = $value;
        }

        return $convertedData;
    }

    /**
     * @param string $stringToEncode
     * @param string $encodingTypes
     *
     * @return string
     */
    private function convToUtf8(
        string $stringToEncode,
        string $encodingTypes = "UTF-8,ASCII,windows-1252,ISO-8859-15,ISO-8859-1"
    ): string {
        $detect = mb_detect_encoding($stringToEncode, $encodingTypes, true);
        if ($detect && $detect !== "UTF-8") {
            if ($detect === 'ISO-8859-15') {
                $stringToEncode = preg_replace('/\x9c/', '|oe|', $stringToEncode);
            }
            $stringToEncode = iconv($detect, "UTF-8", $stringToEncode);
            if ($detect === 'ISO-8859-15') {
                $stringToEncode = preg_replace('/\|oe\|/', 'œ', $stringToEncode);
            }
        }

        return $stringToEncode;
    }

    /**
     * @param Order $order
     *
     * @return $this
     */
    public function setOrder(Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }
}
