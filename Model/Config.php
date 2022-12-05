<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\FakeEntityCreator\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @inheritDoc
 */
class Config implements ConfigInterface
{
    private $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultEmailDomain(): ?string
    {
        $parse = parse_url($this->scopeConfig->getValue(self::XML_PATH_DEFAULT_EMAIL_DOMAIN) ?: '');
        return $parse['host'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function isActiveOrderEntity(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_ORDER_ENTITY_IS_ACTIVE);
    }

    /**
     * @inheritDoc
     */
    public function getProcessBatchSize(): int
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_ORDER_ENTITY_PROCESS_BATCH_SIZE) ?: 2;
    }

    /**
     * @inheritDoc
     */
    public function isActiveOrderEntityInvoice(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_ORDER_ENTITY_INVOICE_IS_ACTIVE);
    }

    /**
     * @inheritDoc
     */
    public function getInvoiceChanceOfBeingCreated(): int
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_ORDER_ENTITY_INVOICE_COBC) ?: 30;
    }

    /**
     * @inheritDoc
     */
    public function isActiveOrderEntityShipment(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_ORDER_ENTITY_SHIPMENT_IS_ACTIVE);
    }

    /**
     * @inheritDoc
     */
    public function getShipmentChanceOfBeingCreated(): int
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_ORDER_ENTITY_SHIPMENT_COBC) ?: 30;
    }

    /**
     * @inheritDoc
     */
    public function getLocaleOptions(): array
    {
        if (!$values = $this->scopeConfig->getValue(self::XML_PATH_LOCAL_OPTIONS)) {
            return [];
        }
        return explode(',', $values);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodList(): array
    {
        if (!$values = $this->scopeConfig->getValue(self::XML_PATH_ORDER_ENTITY_PAYMENT_METHOD_LIST)) {
            return [];
        }
        return explode(',', $values);
    }

    /**
     * @inheritDoc
     */
    public function getShippingMethodList(): array
    {
        if (!$values = $this->scopeConfig->getValue(self::XML_PATH_ORDER_ENTITY_SHIPPING_METHOD_LIST)) {
            return [];
        }
        return array_filter(
            explode(',', $values)
        );
    }
}
