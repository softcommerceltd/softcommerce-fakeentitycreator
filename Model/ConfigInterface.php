<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\FakeEntityCreator\Model;

/**
 * Class ConfigInterface used to obtain configuration data.
 */
interface ConfigInterface
{
    public const XML_PATH_LOCAL_OPTIONS = 'fakeentitycreator/general/locale';
    public const XML_PATH_ORDER_ENTITY_IS_ACTIVE = 'fakeentitycreator/order_entity/is_active';
    public const XML_PATH_ORDER_ENTITY_PROCESS_BATCH_SIZE = 'fakeentitycreator/order_entity/process_batch_size';
    public const XML_PATH_ORDER_ENTITY_INVOICE_IS_ACTIVE = 'fakeentitycreator/order_entity/is_active_invoice';
    public const XML_PATH_ORDER_ENTITY_INVOICE_COBC = 'fakeentitycreator/order_entity/invoice_cobc';
    public const XML_PATH_ORDER_ENTITY_SHIPMENT_IS_ACTIVE = 'fakeentitycreator/order_entity/is_active_shipment';
    public const XML_PATH_ORDER_ENTITY_SHIPMENT_COBC = 'fakeentitycreator/order_entity/shipment_cobc';
    public const XML_PATH_ORDER_ENTITY_PAYMENT_METHOD_LIST = 'fakeentitycreator/order_entity/payment_methods';
    public const XML_PATH_ORDER_ENTITY_SHIPPING_METHOD_LIST = 'fakeentitycreator/order_entity/shipping_methods';

    /**
     * @return bool
     */
    public function isActiveOrderEntity(): bool;

    /**
     * @return int
     */
    public function getProcessBatchSize(): int;

    /**
     * @return bool
     */
    public function isActiveOrderEntityInvoice(): bool;

    /**
     * @return int
     */
    public function getInvoiceChanceOfBeingCreated(): int;

    /**
     * @return bool
     */
    public function isActiveOrderEntityShipment(): bool;

    /**
     * @return int
     */
    public function getShipmentChanceOfBeingCreated(): int;

    /**
     * @return array
     */
    public function getLocaleOptions(): array;

    /**
     * @return array
     */
    public function getPaymentMethodList(): array;

    /**
     * @return array
     */
    public function getShippingMethodList(): array;
}
