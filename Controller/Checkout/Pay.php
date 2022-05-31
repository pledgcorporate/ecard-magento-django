<?php

namespace Pledg\PledgPaymentGateway\Controller\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Pledg\PledgPaymentGateway\Helper\Config;
use Psr\Log\LoggerInterface;

class Pay extends CheckoutAction
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context         $context
     * @param Session         $checkoutSession
     * @param OrderFactory    $orderFactory
     * @param PageFactory     $pageFactory
     * @param Config          $configHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderFactory $orderFactory,
        PageFactory $pageFactory,
        Config $configHelper,
        LoggerInterface $logger
    ) {
        parent::__construct($context, $checkoutSession, $orderFactory);

        $this->pageFactory = $pageFactory;
        $this->configHelper = $configHelper;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $order = $this->getLastOrder([Order::STATE_NEW]);

            $merchantApiKey = $this->configHelper->getMerchantIdForOrder($order);
            if ($merchantApiKey === null) {
                throw new \Exception(sprintf(
                    'Could not retrieve api key for country %s on order %s',
                    $order->getBillingAddress()->getCountryId(),
                    $order->getIncrementId()
                ));
            }

            $order->setState(Order::STATE_PENDING_PAYMENT);
            $order->addStatusToHistory(
                $order->getConfig()->getStateDefaultStatus(Order::STATE_PENDING_PAYMENT),
                __('Customer accessed payment page')
            );
            $order->save();

            $title = __('Pay your order #%1 with Django', $order->getIncrementId());
            $page = $this->pageFactory->create();
            $page->getConfig()->getTitle()->set($title);

            $pageMainTitle = $page->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($title);
            }

            $page->getLayout()->getBlock('pledg_payment_gateway_checkout_pay')->setOrder($order);

            return $page;
        } catch (\Exception $e) {
            $this->logger->error('An error occurred on Django payment page', [
                'exception' => $e,
            ]);

            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your payment. Please try again.')
            );

            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
    }
}
