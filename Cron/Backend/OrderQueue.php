<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\FakeEntityCreator\Cron\Backend;

use Faker;
use SoftCommerce\FakeEntityCreator\Model\ConfigInterface;
use SoftCommerce\FakeEntityCreator\Model\InvoiceServiceInterface;
use SoftCommerce\FakeEntityCreator\Model\OrderServiceInterface;

/**
 * Class OrderQueue used to create order entity
 * over cron schedule.
 */
class OrderQueue
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var OrderServiceInterface
     */
    private $orderService;

    /**
     * @var InvoiceServiceInterface
     */
    private $invoiceService;

    /**
     * @var Faker\Generator
     */
    private $faker;

    /**
     * OrderQueue constructor.
     * @param ConfigInterface $config
     * @param OrderServiceInterface $orderService
     * @param InvoiceServiceInterface $invoiceService
     * @param Faker\Factory $fakerFactory
     */
    public function __construct(
        ConfigInterface $config,
        OrderServiceInterface $orderService,
        InvoiceServiceInterface $invoiceService,
        Faker\Factory $fakerFactory
    ) {
        $this->config = $config;
        $this->orderService = $orderService;
        $this->invoiceService = $invoiceService;
        $this->faker = $fakerFactory->create();
    }

    /**
     * @return void
     * @throws \Zend_Log_Exception
     */
    public function execute(): void
    {
        if (!$this->config->isActiveOrderEntity()) {
            return;
        }

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/fake-order.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $result = [];
        $count = $this->config->getProcessBatchSize();
        for ($i = 1; $i <= $count; $i++) {
            try {
                $this->orderService->execute();
                if (!$order = $this->orderService->getOrder()) {
                    continue;
                }
                $result['order'][] = $order->getIncrementId();
                if ($this->faker->boolean($this->config->getInvoiceChanceOfBeingCreated())) {
                    $this->invoiceService->execute($order);
                    if ($invoice = $this->invoiceService->getInvoice()) {
                        $result['invoice'][] = $invoice->getIncrementId();
                    }
                }
            } catch (\Exception $e) {
                $logger->debug(print_r([
                    __METHOD__ => ['error' => $e->getMessage()]
                ], true), []);
            }
        }

        if (!empty($result)) {
            $logger->debug(print_r([
                __METHOD__ => $result
            ], true), []);
        }
    }
}
