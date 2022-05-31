<?php

namespace Pledg\PledgPaymentGateway\Model\Ui;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Pledg\PledgPaymentGateway\Gateway\Config\Config1;
use Pledg\PledgPaymentGateway\Gateway\Config\Config2;
use Pledg\PledgPaymentGateway\Gateway\Config\Config3;
use Pledg\PledgPaymentGateway\Gateway\Config\Config4;
use Pledg\PledgPaymentGateway\Gateway\Config\Config5;
use Pledg\PledgPaymentGateway\Gateway\Config\Config6;
use Pledg\PledgPaymentGateway\Gateway\Config\Config7;
use Pledg\PledgPaymentGateway\Gateway\Config\Config8;
use Pledg\PledgPaymentGateway\Gateway\Config\Config9;
use Pledg\PledgPaymentGateway\Gateway\Config\Config10;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const LOGO_DIR_UPLOAD = 'sales/pledg/logo';

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Repository            $assetRepo
     * @param PaymentHelper         $paymentHelper
     * @param RequestInterface      $request
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Repository $assetRepo,
        PaymentHelper $paymentHelper,
        RequestInterface $request,
        StoreManagerInterface $storeManager
    ) {
        $this->assetRepo = $assetRepo;
        $this->paymentHelper = $paymentHelper;
        $this->request = $request;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        $defaultLogoUrl = $this->assetRepo->getUrlWithParams('Pledg_PledgPaymentGateway::images/django_logo.png', [
            '_secure' => $this->request->isSecure(),
        ]);
        $mediaBaseUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA, $this->request->isSecure())  . self::LOGO_DIR_UPLOAD . '/';

        $availableMethods = [];
        foreach ($this->getPaymentMethodCodes() as $methodCode) {
            $method = $this->paymentHelper->getMethodInstance($methodCode);
            if (!$method->isAvailable()) {
                continue;
            }

            $methodLogo = $defaultLogoUrl;
            if (strlen($method->getConfigData('gateway_logo')) > 0) {
                $methodLogo = $mediaBaseUrl . $method->getConfigData('gateway_logo');
            }

            $availableMethods[$methodCode] = [
                'title'        => $method->getConfigData('title'),
                'description'  => $method->getConfigData('description'),
                'logo'         => $methodLogo,
            ];
        }

        if (count($availableMethods) === 0) {
            return [];
        }

        return [
            'payment' => $availableMethods,
        ];
    }

    /**
     * @return array
     */
    public static function getPaymentMethodCodes(): array
    {
        return [
            Config1::CODE,
            Config2::CODE,
            Config3::CODE,
            Config4::CODE,
            Config5::CODE,
            Config6::CODE,
            Config7::CODE,
            Config8::CODE,
            Config9::CODE,
            Config10::CODE,
        ];
    }
}
