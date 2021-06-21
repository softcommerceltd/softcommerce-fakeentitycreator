<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\FakeEntityCreator\Model;

use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Service;

/**
 * @inheritdoc
 */
class InvoiceService implements InvoiceServiceInterface
{
    /**
     * @var InvoiceInterface
     */
    private $invoice;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * InvoiceService constructor.
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param Service\InvoiceService $invoiceService
     * @param Transaction $transaction
     */
    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        Service\InvoiceService $invoiceService,
        Transaction $transaction
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
    }

    /**
     * @inheritdoc
     */
    public function execute(OrderInterface $order): void
    {
        if (!$order->canInvoice()) {
            throw new LocalizedException(__('Cannot invoice'));
        }

        $this->invoice = $this->invoiceService->prepareInvoice($order);
        $this->getInvoice()->register();
        $this->invoiceRepository->save($this->getInvoice());

        $order->setIsInProcess(true);

        $this->transaction
            ->addObject($this->getInvoice())
            ->addObject($this->getInvoice()->getOrder())
            ->save();

        $order->addCommentToStatusHistory(
            __('Invoice ID: # %1 (created programmatically).', $this->invoice->getEntityId())
        )->setIsCustomerNotified(false)
            ->save();
    }

    /**
     * @inheritdoc
     */
    public function getInvoice(): ?InvoiceInterface
    {
        return $this->invoice;
    }
}
