<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\FakeEntityCreator\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Model\Order;

/**
 * Class InvoiceService used to create invoice document.
 */
interface InvoiceServiceInterface
{
    /**
     * @param OrderInterface|Order $order
     * @throws LocalizedException
     */
    public function execute(OrderInterface $order): void;

    /**
     * @return InvoiceInterface|null
     */
    public function getInvoice(): ?InvoiceInterface;
}
