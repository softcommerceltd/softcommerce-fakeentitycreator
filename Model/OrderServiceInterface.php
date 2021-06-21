<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\FakeEntityCreator\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Plenty\Order\Model\Order;
use SoftCommerce\FakeEntityCreator\Model\OrderService\OrderRequestCriteriaInterface;

/**
 * Class OrderServiceInterface used to create sales order.
 */
interface OrderServiceInterface
{
    /**
     * @param OrderRequestCriteriaInterface|null $requestCriteria
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(?OrderRequestCriteriaInterface $requestCriteria = null): void;

    /**
     * @return OrderInterface|Order|null
     */
    public function getOrder(): ?OrderInterface;

    /**
     * @return CartInterface|Quote|null
     */
    public function getQuote(): ?CartInterface;
}
