<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\FakeEntityCreator\Model\OrderService;

use Faker;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use SoftCommerce\FakeEntityCreator\Model\ConfigInterface;

/**
 * @inheritDoc
 */
class OrderRequestCriteriaBuilder extends OrderRequestCriteria implements OrderRequestCriteriaBuilderInterface
{
    /**
     * @var OrderRequestCriteriaInterfaceFactory
     */
    private $objectFactory;

    /**
     * OrderRequestCriteriaBuilder constructor.
     * @param OrderRequestCriteriaInterfaceFactory $requestCriteriaFactory
     * @param ConfigInterface $config
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param Faker\Factory $fakerFactory
     * @param ResourceConnection $resourceConnection
     * @param ScopeConfigInterface $scopeConfig
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        OrderRequestCriteriaInterfaceFactory $requestCriteriaFactory,
        ConfigInterface $config,
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        Faker\Factory $fakerFactory,
        ResourceConnection $resourceConnection,
        ScopeConfigInterface $scopeConfig,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->objectFactory = $requestCriteriaFactory;
        parent::__construct(
            $config,
            $customerFactory,
            $customerRepository,
            $fakerFactory,
            $resourceConnection,
            $scopeConfig,
            $stockIndexTableNameResolver,
            $storeManager,
            $data
        );
    }

    /**
     * @return OrderRequestCriteriaInterface
     */
    public function create(): OrderRequestCriteriaInterface
    {
        $object = $this->objectFactory->create(['data' => $this->getData()]);
        $this->resetData();
        return $object;
    }
}
