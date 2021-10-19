<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\FakeEntityCreator\Model;

use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderInterface;
use SoftCommerce\FakeEntityCreator\Model\OrderService\OrderRequestCriteriaBuilderInterface;
use SoftCommerce\FakeEntityCreator\Model\OrderService\OrderRequestCriteriaInterface;

/**
 * @inheritDoc
 */
class OrderService implements OrderServiceInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var OrderInterface|null
     */
    private $order;

    /**
     * @var CartInterface|null
     */
    private $quote;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var OrderRequestCriteriaInterface
     */
    private $requestCriteria;

    /**
     * @var OrderRequestCriteriaBuilderInterface
     */
    private $requestCriteriaBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * OrderService constructor.
     * @param CartRepositoryInterface $cartRepository
     * @param ManagerInterface $eventManager
     * @param OrderRequestCriteriaBuilderInterface $requestCriteriaBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param QuoteFactory $quoteFactory
     * @param QuoteManagement $quoteManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ManagerInterface $eventManager,
        OrderRequestCriteriaBuilderInterface $requestCriteriaBuilder,
        ProductRepositoryInterface $productRepository,
        QuoteFactory $quoteFactory,
        QuoteManagement $quoteManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->cartRepository = $cartRepository;
        $this->eventManager = $eventManager;
        $this->requestCriteriaBuilder = $requestCriteriaBuilder;
        $this->productRepository = $productRepository;
        $this->quoteFactory = $quoteFactory;
        $this->quoteManagement = $quoteManagement;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function execute(?OrderRequestCriteriaInterface $requestCriteria = null): void
    {
        if (null === $requestCriteria) {
            $requestCriteria = $this->requestCriteriaBuilder->create();
        }

        $this->requestCriteria = $requestCriteria;
        $this->createQuote()
            ->addProducts()
            ->addAddress()
            ->addShippingRates()
            ->addPayment()
            ->collectTotals()
            ->createOrder();
    }

    /**
     * @inheritDoc
     */
    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    /**
     * @inheritDoc
     */
    public function getQuote(): ?CartInterface
    {
        return $this->quote;
    }

    /**
     * @return $this
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function createQuote()
    {
        $this->quote = $this->quoteFactory
            ->create()
            ->setStore($this->requestCriteria->getStore())
            ->assignCustomer($this->requestCriteria->getCustomer())
            ->setCurrency()
            ->setInventoryProcessed(false);
        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function addProducts()
    {
        foreach ($this->getProducts()->getItems() as $item) {
            try {
                $this->getQuote()->addProduct($item, $this->requestCriteria->getItemQty());
            } catch (\Exception $e) {
                throw new LocalizedException(
                    __('Could not add item SKU: %1. Reason: %2', $item->getSku(), $e->getMessage())
                );
            }
        }
        return $this;
    }

    /**
     * @return $this
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function addAddress()
    {
        $this->getQuote()
            ->getBillingAddress()
            ->addData($this->requestCriteria->getAddress());

        $this->getQuote()
            ->getShippingAddress()
            ->addData($this->requestCriteria->getAddress(OrderRequestCriteriaInterface::SHIPPING_ADDRESS));
        return $this;
    }

    /**
     * @return $this
     */
    private function addShippingRates()
    {
        $shippingAddress = $this->getQuote()->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($this->requestCriteria->getShippingMethod());
        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    private function addPayment()
    {
        $this->getQuote()->setPaymentMethod($this->requestCriteria->getPaymentMethod());

        $this->cartRepository->save($this->getQuote());

        $request = ['method' => $this->getQuote()->getPaymentMethod()];
        if ($this->requestCriteria->getPaymentMethod() === 'purchaseorder') {
            $request['po_number'] = hash('md5', date('Y-m-d'));
        }

        $this->getQuote()->getPayment()->importData($request);
        return $this;
    }

    /**
     * @return $this
     */
    private function collectTotals()
    {
        $this->getQuote()->collectTotals();
        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    private function createOrder()
    {
        $this->cartRepository->save($this->getQuote());
        $this->order = $this->quoteManagement->submit($this->getQuote());
        $this->eventManager->dispatch('sales_order_place_after', ['order' => $this->getOrder()]);
        $this->getOrder()->setEmailSent(0);
        return $this;
    }

    /**
     * @return ProductSearchResultsInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getProducts(): ProductSearchResultsInterface
    {
        return $this->productRepository->getList(
            $this->searchCriteriaBuilder
                ->addFilter('sku', $this->requestCriteria->getItemSku(), 'in')
                ->create()
        );
    }
}
