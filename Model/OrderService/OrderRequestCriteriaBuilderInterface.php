<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SoftCommerce\FakeEntityCreator\Model\OrderService;

/**
 * Interface OrderRequestCriteriaBuilderInterface used to build
 * order request criteria.
 */
interface OrderRequestCriteriaBuilderInterface extends OrderRequestCriteriaInterface
{
    /**
     * @return OrderRequestCriteriaInterface
     */
    public function create(): OrderRequestCriteriaInterface;
}
