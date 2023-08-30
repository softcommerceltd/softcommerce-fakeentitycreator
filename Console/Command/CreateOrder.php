<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\FakeEntityCreator\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Store\Model\StoreManagerInterface;
use SoftCommerce\FakeEntityCreator\Model\InvoiceServiceInterfaceFactory;
use SoftCommerce\FakeEntityCreator\Model\OrderService\OrderRequestCriteriaBuilderInterfaceFactory;
use SoftCommerce\FakeEntityCreator\Model\OrderServiceInterfaceFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateOrder used to create
 * fake order entity.
 */
class CreateOrder extends Command
{
    private const COMMAND_NAME = 'faker:order';
    private const COUNTER_FLAG = 'counter';
    private const INVOICE_FLAG = 'invoice';
    private const SKU_FILTER = 'sku';
    private const STORE_FILTER = 'store';

    /**
     * @var State
     */
    private State $appState;

    /**
     * @var OrderRequestCriteriaBuilderInterfaceFactory
     */
    private OrderRequestCriteriaBuilderInterfaceFactory $requestCriteriaBuilderFactory;

    /**
     * @var OrderServiceInterfaceFactory
     */
    private OrderServiceInterfaceFactory $orderServiceFactory;

    /**
     * @var InvoiceServiceInterfaceFactory
     */
    private InvoiceServiceInterfaceFactory $invoiceServiceFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param State $appState
     * @param OrderRequestCriteriaBuilderInterfaceFactory $requestCriteriaBuilderFactory
     * @param OrderServiceInterfaceFactory $orderServiceFactory
     * @param InvoiceServiceInterfaceFactory $invoiceServiceFactory
     * @param StoreManagerInterface $storeManager
     * @param string|null $name
     */
    public function __construct(
        State $appState,
        OrderRequestCriteriaBuilderInterfaceFactory $requestCriteriaBuilderFactory,
        OrderServiceInterfaceFactory $orderServiceFactory,
        InvoiceServiceInterfaceFactory $invoiceServiceFactory,
        StoreManagerInterface $storeManager,
        ?string $name = null
    ) {
        $this->appState = $appState;
        $this->requestCriteriaBuilderFactory = $requestCriteriaBuilderFactory;
        $this->orderServiceFactory = $orderServiceFactory;
        $this->invoiceServiceFactory = $invoiceServiceFactory;
        $this->storeManager = $storeManager;
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Create Fake Order')
            ->setDefinition([
                new InputOption(
                    self::COUNTER_FLAG,
                    '-c',
                    InputOption::VALUE_REQUIRED,
                    'Counter Flag. E.g. Number of orders to be created.'
                ),
                new InputOption(
                    self::INVOICE_FLAG,
                    '-i',
                    InputOption::VALUE_NONE,
                    'Invoice Flag.'
                ),
                new InputOption(
                    self::STORE_FILTER,
                    '-w',
                    InputOption::VALUE_REQUIRED,
                    'Store ID Filter.'
                ),
                new InputOption(
                    self::SKU_FILTER,
                    '-s',
                    InputOption::VALUE_REQUIRED,
                    'SKU Filter.'
                ),
            ]);

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->appState->setAreaCode(Area::AREA_ADMINHTML);

        $orderService = $this->orderServiceFactory->create();
        $invoiceService = $this->invoiceServiceFactory->create();
        $requestCriteria = $this->requestCriteriaBuilderFactory->create();
        $requestCriteria = $requestCriteria->create();

        if ($sku = $input->getOption(self::SKU_FILTER)) {
            $requestCriteria->setItemSku(explode(',', $sku));
        }

        $storeId = $input->getOption(self::STORE_FILTER);
        if (null !== $storeId) {
            try {
                $storeCode = $this->storeManager->getStore($storeId)->getCode();
            } catch (\Exception $e) {
                $storeCode = 'admin';
            }
            $requestCriteria->setStoreCode($storeCode);
        }

        $count = $input->getOption(self::COUNTER_FLAG) ?: 1;
        for ($i=1; $i <= $count; $i++) {
            try {
                $orderService->execute();
                if (!$order = $orderService->getOrder()) {
                    continue;
                }

                $output->writeln(
                    sprintf(
                        '<info>New Order:</info> <comment># %s ::: %s</comment>',
                        $order->getEntityId(),
                        $order->getIncrementId()
                    )
                );
                if (!$input->getOption(self::INVOICE_FLAG)) {
                    continue;
                }

                $invoiceService->execute($order);
                if ($invoice = $invoiceService->getInvoice()) {
                    $output->writeln(sprintf('<info>New Invoice: # %s.</info>', $invoice->getIncrementId()));
                }
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            }
        }
        if ($count) {
            $output->writeln(sprintf('<info>A total of %s orders have been created.</info>', $count));
        } else {
            $output->writeln('<note>No orders have been created.</note>');
        }

        return Cli::RETURN_SUCCESS;
    }
}
