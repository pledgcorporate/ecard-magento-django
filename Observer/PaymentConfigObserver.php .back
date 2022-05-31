<?php

namespace Pledg\PledgPaymentGateway\Observer;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Message\ManagerInterface;
use Pledg\PledgPaymentGateway\Model\Ui\ConfigProvider;

class PaymentConfigObserver implements ObserverInterface
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @param Http             $request
     * @param ManagerInterface $messageManager
     * @param WriterInterface  $configWriter
     */
    public function __construct(
        Http $request,
        ManagerInterface $messageManager,
        WriterInterface $configWriter
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->configWriter = $configWriter;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $postParams = $this->request->getPost();

        if (!isset($postParams['config_state'])) {
            return;
        }

        $groups = $postParams['groups'];
        foreach (ConfigProvider::getPaymentMethodCodes() as $paymentMethodCode) {
            if ($this->canProcessSection($postParams, $paymentMethodCode)) {
                $fields = $groups[$paymentMethodCode]['fields'];
                if (!empty($fields['active']['value']) && array_key_exists('api_key_mapping', $fields)) {
                    $countryMapping = $fields['api_key_mapping']['value'] ?? [];
                    $hasError = false;
                    $countries = [];
                    foreach ($countryMapping as $row) {
                        if (!isset($row['country'])) {
                            // Magento adds an empty line
                            continue;
                        }
                        if (empty($row['country'])) {
                            $hasError = true;
                            $this->messageManager->addErrorMessage(
                                __('Please select a country on Django payment method %1', $paymentMethodCode)
                            );
                        }
                        if (empty($row['api_key'])) {
                            $hasError = true;
                            $this->messageManager->addErrorMessage(
                                __('Please fill in an api key on Django payment method %1', $paymentMethodCode)
                            );
                        }

                        if (in_array($row['country'], $countries)) {
                            $hasError = true;
                            $this->messageManager->addErrorMessage(__(
                                'Please remove duplicate mapping for country %1 on Django payment method %2',
                                $row['country'],
                                $paymentMethodCode
                            ));
                        }

                        $countries[] = $row['country'];
                    }

                    if (count($countries) === 0) {
                        $hasError = true;
                        $this->messageManager->addErrorMessage(__(
                            'You must select at least one country to be able to activate Django payment method %1',
                            $paymentMethodCode
                        ));
                    }

                    if ($hasError) {
                        $groups[$paymentMethodCode]['fields']['active']['value'] = 0;
                        continue;
                    }

                    $this->configWriter->save(sprintf('payment/%s/allowspecific', $paymentMethodCode), 1);
                    $this->configWriter->save(sprintf('payment/%s/specificcountry', $paymentMethodCode), implode(',', $countries));
                }
            }
        }

        $this->request->setPostValue('groups', $groups);
    }

    /**
     * @param array  $postParams
     * @param string $sectionCode
     *
     * @return bool
     */
    private function canProcessSection($postParams, $sectionCode)
    {
        $sections = $postParams['config_state'];
        foreach (array_keys($sections) as $sectionKey) {
            if (strpos($sectionKey, $sectionCode) !== false) {
                if (isset($postParams['groups'][$sectionCode]['fields'])) {
                    return true;
                }
            }
        }

        return false;
    }
}
