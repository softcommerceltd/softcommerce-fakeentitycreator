<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\FakeEntityCreator\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Payment\Model\Config;

/**
 * Class PaymentMethod used to provide
 * payment method options.
 */
class PaymentMethod implements OptionSourceInterface
{
    /**
     * @var Config
     */
    private $paymentConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Payment constructor.
     * @param Config $paymentConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Config $paymentConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->paymentConfig = $paymentConfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->paymentConfig->getActiveMethods() as $code => $model) {
            $options[] = [
                'label' => $this->scopeConfig->getValue('payment/'.$code.'/title') ?: $code,
                'value' => $code,
            ];
        }
        return $options;
    }
}
