<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\FakeEntityCreator\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @inheritdoc
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
     * @inheritdoc
     */
    public function isActiveOrderEntity(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_ORDER_ENTITY_IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function getProcessBatchSize(): int
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_ORDER_ENTITY_PROCESS_BATCH_SIZE) ?: 2;
    }

    /**
     * @inheritdoc
     */
    public function isActiveOrderEntityInvoice(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_ORDER_ENTITY_INVOICE_IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function getInvoiceChanceOfBeingCreated(): int
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_ORDER_ENTITY_INVOICE_COBC) ?: 30;
    }

    /**
     * @inheritdoc
     */
    public function isActiveOrderEntityShipment(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_ORDER_ENTITY_SHIPMENT_IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function getShipmentChanceOfBeingCreated(): int
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_ORDER_ENTITY_SHIPMENT_COBC) ?: 30;
    }

    /**
     * @inheritdoc
     */
    public function getLocaleOptions(): array
    {
        if (!$values = $this->scopeConfig->getValue(self::XML_PATH_LOCAL_OPTIONS)) {
            return [];
        }
        return explode(',', $values);
    }

    /**
     * @inheritdoc
     */
    public function getPaymentMethodList(): array
    {
        if (!$values = $this->scopeConfig->getValue(self::XML_PATH_ORDER_ENTITY_PAYMENT_METHOD_LIST)) {
            return [];
        }
        return explode(',', $values);
    }

    /**
     * @inheritdoc
     */
    public function getShippingMethodList(): array
    {
        if (!$values = $this->scopeConfig->getValue(self::XML_PATH_ORDER_ENTITY_SHIPPING_METHOD_LIST)) {
            return [];
        }
        return explode(',', $values);
    }
}
